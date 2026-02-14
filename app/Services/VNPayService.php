<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class VNPayService
{
    protected $tmnCode;
    protected $hashSecret;
    protected $url;
    protected $returnUrl;
    protected $apiUrl;

    public function __construct()
    {
        $this->tmnCode = config('services.vnpay.tmn_code');
        $this->hashSecret = config('services.vnpay.hash_secret');
        $this->url = config('services.vnpay.url');
        $this->apiUrl = config('services.vnpay.api_url');

        // Tự động tạo return URL từ APP_URL thay vì dùng IP local cố định
        $configReturnUrl = config('services.vnpay.return_url');
        if ($configReturnUrl && !$this->isLocalUrl($configReturnUrl)) {
            $this->returnUrl = $configReturnUrl;
        } else {
            // Fallback: tạo URL động từ APP_URL + route path
            $this->returnUrl = rtrim(config('app.url'), '/') . '/payment/vnpay/callback';
        }
    }

    /**
     * Kiểm tra URL có phải local không (192.168.x.x, 127.0.0.1, localhost)
     */
    protected function isLocalUrl(string $url): bool
    {
        $host = parse_url($url, PHP_URL_HOST);
        if (!$host) return true;
        
        return in_array($host, ['localhost', '127.0.0.1']) 
            || str_starts_with($host, '192.168.')
            || str_starts_with($host, '10.')
            || str_starts_with($host, '172.');
    }

    /**
     * Tạo URL thanh toán VNPay
     */
    public function createPaymentUrl($booking, $amount, $orderInfo = '', $returnUrl = null)
    {
        $vnp_TxnRef = $booking->id . '_' . time();
        $vnp_OrderInfo = $orderInfo ?: "Thanh toán booking #{$booking->id}";
        $vnp_OrderType = 'billpayment';
        $vnp_Amount = $amount * 100; // VNPay yêu cầu số tiền * 100
        $vnp_Locale = 'vn';
        $vnp_BankCode = ''; // Để trống để hiển thị tất cả ngân hàng
        $vnp_IpAddr = request()->ip();
        $vnp_ReturnUrl = $returnUrl ?? $this->returnUrl;

        $inputData = [
            "vnp_Version" => "2.1.0",
            "vnp_TmnCode" => $this->tmnCode,
            "vnp_Amount" => $vnp_Amount,
            "vnp_Command" => "pay",
            "vnp_CreateDate" => date('YmdHis'),
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => $vnp_IpAddr,
            "vnp_Locale" => $vnp_Locale,
            "vnp_OrderInfo" => $vnp_OrderInfo,
            "vnp_OrderType" => $vnp_OrderType,
            "vnp_ReturnUrl" => $vnp_ReturnUrl,
            "vnp_TxnRef" => $vnp_TxnRef,
        ];

        if ($vnp_BankCode != "") {
            $inputData['vnp_BankCode'] = $vnp_BankCode;
        }

        ksort($inputData);
        $query = "";
        $i = 0;
        $hashdata = "";

        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashdata .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
            $query .= urlencode($key) . "=" . urlencode($value) . '&';
        }

        $vnp_Url = $this->url . "?" . $query;
        
        if (isset($this->hashSecret)) {
            $vnpSecureHash = hash_hmac('sha512', $hashdata, $this->hashSecret);
            $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
        }

        return $vnp_Url;
    }

    /**
     * Xác thực callback từ VNPay
     */
    public function validateCallback($request)
    {
        $vnp_SecureHash = $request->vnp_SecureHash;
        $inputData = $request->all();
        
        unset($inputData['vnp_SecureHash']);
        ksort($inputData);
        
        $hashData = "";
        $i = 0;
        
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashData .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashData .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
        }

        $secureHash = hash_hmac('sha512', $hashData, $this->hashSecret);

        return $secureHash === $vnp_SecureHash;
    }

    /**
     * Parse booking ID từ TxnRef
     */
    public function parseBookingId($vnp_TxnRef)
    {
        return explode('_', $vnp_TxnRef)[0];
    }

    /**
     * Kiểm tra có đang dùng VNPay Sandbox hay không
     */
    public function isSandbox(): bool
    {
        return str_contains($this->apiUrl ?? '', 'sandbox');
    }

    /**
     * Gọi API Refund VNPay Sandbox
     * Docs: https://sandbox.vnpayment.vn/apis/docs/thanh-toan-pay/pay.html#refund
     * 
     * LƯU Ý: VNPay Sandbox KHÔNG hỗ trợ API hoàn tiền thực tế.
     * Sandbox luôn trả về mã lỗi 99 "NAM system init refund error".
     * Khi phát hiện sandbox mode, hệ thống sẽ tự động mô phỏng hoàn tiền thành công.
     * 
     * @param \App\Models\Booking $booking
     * @param float $amount Số tiền cần hoàn
     * @param string $transactionRef Mã giao dịch VNPay gốc (vnp_TransactionNo)
     * @param string $txnRef Mã đơn hàng gốc (vnp_TxnRef) 
     * @param string $transactionDate Ngày giao dịch gốc (yyyyMMddHHmmss)
     * @param string $createdBy Người tạo yêu cầu hoàn tiền
     * @return array ['success' => bool, 'message' => string, 'data' => array]
     */
    public function refund($booking, float $amount, string $transactionRef, string $txnRef, string $transactionDate, string $createdBy = 'Admin'): array
    {
        try {
            // vnp_RequestId: Alphanumeric[1,32] - KHÔNG dùng UUID (36 ký tự có dấu '-')
            // Phải là tối đa 32 ký tự chữ+số
            $vnp_RequestId = str_replace('-', '', Str::uuid()->toString()); // 32 hex chars
            $vnp_Version = '2.1.0';
            $vnp_Command = 'refund';
            $vnp_TmnCode = $this->tmnCode;
            $vnp_TransactionType = '02'; // 02 = Hoàn tiền toàn phần, 03 = Hoàn tiền một phần
            $vnp_TxnRef = $txnRef;
            $vnp_Amount = (string)((int)($amount * 100)); // VNPay yêu cầu Numeric dạng string
            // vnp_OrderInfo: Không bao gồm ký tự đặc biệt (VNPay docs)
            $vnp_OrderInfo = "Hoan tien booking " . $booking->id;
            $vnp_TransactionNo = $transactionRef;
            $vnp_TransactionDate = $transactionDate;
            $vnp_CreateDate = date('YmdHis');
            $vnp_CreateBy = $createdBy;
            $vnp_IpAddr = request()->ip() ?: '127.0.0.1';

            // Tạo chuỗi hash theo thứ tự VNPay quy định
            // Tất cả giá trị đã là string
            $hashData = implode('|', [
                $vnp_RequestId,
                $vnp_Version,
                $vnp_Command,
                $vnp_TmnCode,
                $vnp_TransactionType,
                $vnp_TxnRef,
                $vnp_Amount,
                $vnp_TransactionNo,
                $vnp_TransactionDate,
                $vnp_CreateBy,
                $vnp_CreateDate,
                $vnp_IpAddr,
                $vnp_OrderInfo,
            ]);

            $vnp_SecureHash = hash_hmac('sha512', $hashData, $this->hashSecret);

            // Tất cả giá trị trong request body phải là STRING (VNPay yêu cầu)
            $requestData = [
                'vnp_RequestId' => $vnp_RequestId,
                'vnp_Version' => $vnp_Version,
                'vnp_Command' => $vnp_Command,
                'vnp_TmnCode' => $vnp_TmnCode,
                'vnp_TransactionType' => $vnp_TransactionType,
                'vnp_TxnRef' => $vnp_TxnRef,
                'vnp_Amount' => $vnp_Amount,
                'vnp_OrderInfo' => $vnp_OrderInfo,
                'vnp_TransactionNo' => $vnp_TransactionNo,
                'vnp_TransactionDate' => $vnp_TransactionDate,
                'vnp_CreateBy' => $vnp_CreateBy,
                'vnp_CreateDate' => $vnp_CreateDate,
                'vnp_IpAddr' => $vnp_IpAddr,
                'vnp_SecureHash' => $vnp_SecureHash,
            ];

            Log::info('VNPay Refund Request', [
                'booking_id' => $booking->id,
                'amount' => $amount,
                'request' => $requestData,
            ]);

            // Gọi API VNPay
            /** @var \Illuminate\Http\Client\Response $response */
            $response = Http::timeout(30)->post($this->apiUrl, $requestData);

            $responseData = $response->json();

            Log::info('VNPay Refund Response', [
                'booking_id' => $booking->id,
                'response' => $responseData,
            ]);

            if (!$responseData) {
                return [
                    'success' => false,
                    'message' => 'Không nhận được phản hồi từ VNPay',
                    'data' => [],
                ];
            }

            $responseCode = $responseData['vnp_ResponseCode'] ?? '99';
            $responseMessage = $this->getRefundResponseMessage($responseCode);

            // VNPay Sandbox KHÔNG hỗ trợ refund API -> mô phỏng thành công
            if ($responseCode === '99' && $this->isSandbox()) {
                Log::warning('VNPay Sandbox does NOT support refund API. Simulating successful refund.', [
                    'booking_id' => $booking->id,
                    'amount' => $amount,
                    'original_response' => $responseData,
                ]);

                return [
                    'success' => true,
                    'message' => 'Hoàn tiền thành công (Sandbox - mô phỏng)',
                    'response_code' => '00',
                    'data' => [
                        'vnp_ResponseCode' => '00',
                        'vnp_Message' => 'Sandbox simulated refund success',
                        'vnp_TxnRef' => $vnp_TxnRef,
                        'vnp_Amount' => $vnp_Amount,
                        'vnp_TransactionNo' => 'SANDBOX_REFUND_' . time(),
                        'vnp_TransactionType' => $vnp_TransactionType,
                        'vnp_Command' => 'refund',
                        'sandbox_simulated' => true,
                    ],
                ];
            }

            // ResponseCode == '00' là thành công
            return [
                'success' => $responseCode === '00',
                'message' => $responseMessage,
                'response_code' => $responseCode,
                'data' => $responseData,
            ];

        } catch (\Exception $e) {
            Log::error('VNPay Refund Error', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Lỗi kết nối VNPay: ' . $e->getMessage(),
                'response_code' => 'ERROR',
                'data' => [],
            ];
        }
    }

    /**
     * Lấy thông tin message phản hồi hoàn tiền VNPay
     */
    protected function getRefundResponseMessage(string $code): string
    {
        return match($code) {
            '00' => 'Hoàn tiền thành công',
            '02' => 'Mã đơn hàng không hợp lệ',
            '03' => 'Dữ liệu gửi sang không đúng định dạng',
            '04' => 'Không tìm thấy giao dịch gốc',
            '91' => 'Không tìm thấy giao dịch hoàn tiền',
            '93' => 'Số tiền hoàn tiền vượt quá số tiền gốc',
            '94' => 'Giao dịch đã được hoàn tiền trước đó',
            '95' => 'Giao dịch không thành công bên VNPay',
            '97' => 'Chữ ký không hợp lệ',
            '99' => 'Lỗi không xác định',
            default => "Lỗi VNPay (mã: {$code})",
        };
    }
}
