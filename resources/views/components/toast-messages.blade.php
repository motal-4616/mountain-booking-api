{{--
    Flash Messages Data Container
    Chứa data để JS Toast/Popup system đọc và hiển thị
--}}

<div id="flash-messages-data" 
    data-success="{{ session('success') ?? '' }}"
    data-error="{{ session('error') ?? '' }}"
    data-warning="{{ session('warning') ?? '' }}"
    data-info="{{ session('info') ?? '' }}"
    data-errors="{{ $errors->any() ? json_encode($errors->all()) : '' }}"
    style="display: none;">
</div>

{{-- Inline Script để đảm bảo toast hiển thị --}}
@if(session('success') || session('error') || session('warning') || session('info') || $errors->any())
<script>
// Chạy ngay lập tức
(function() {
    let attempts = 0;
    const maxAttempts = 50;
    
    function tryShowToast() {
        attempts++;
        
        if (typeof window.Toast === 'undefined') {
            if (attempts < maxAttempts) {
                setTimeout(tryShowToast, 100);
            }
            return;
        }
        
        @if(session('success'))
        window.Toast.success(@json(session('success')));
        @endif
        
        @if(session('error'))
        window.Toast.error(@json(session('error')));
        @endif
        
        @if(session('warning'))
        window.Toast.warning(@json(session('warning')));
        @endif
        
        @if(session('refund_debug'))
        // Hiển thị debug info hoàn tiền
        const debugInfo = @json(session('refund_debug'));
        console.error('=== REFUND DEBUG INFO ===');
        console.error('Booking ID:', debugInfo.booking_id);
        console.error('Paid Amount:', debugInfo.paid_amount);
        console.error('Refund Status:', debugInfo.refund_status);
        console.error('Refund Message:', debugInfo.refund_message);
        console.error('Payment Records:', debugInfo.payments);
        console.error('Recent VNPay Logs:', debugInfo.recent_logs);
        console.error('========================');
        
        // Alert cho dễ nhìn
        let debugMsg = '=== DEBUG HOÀN TIỀN ===\n';
        debugMsg += 'Booking: #' + debugInfo.booking_id + '\n';
        debugMsg += 'Số tiền: ' + debugInfo.paid_amount + '\n';
        debugMsg += 'Trạng thái: ' + debugInfo.refund_status + '\n';
        debugMsg += 'Lỗi: ' + debugInfo.refund_message + '\n\n';
        if (debugInfo.payments && debugInfo.payments.length > 0) {
            debugMsg += '--- Payment Records ---\n';
            debugInfo.payments.forEach(function(p, i) {
                debugMsg += 'Payment #' + p.id + ':\n';
                debugMsg += '  amount: ' + p.amount + '\n';
                debugMsg += '  transaction_ref (vnp_TxnRef): ' + (p['transaction_ref (vnp_TxnRef)'] || 'NULL') + '\n';
                debugMsg += '  vnp_transaction_no: ' + (p.vnp_transaction_no || 'NULL') + '\n';
                debugMsg += '  created_at: ' + (p.created_at || 'NULL') + '\n';
            });
        }
        if (debugInfo.recent_logs) {
            debugMsg += '\n--- VNPay Logs ---\n' + debugInfo.recent_logs;
        }
        alert(debugMsg);
        @endif
        
        @if(session('info'))
        window.Toast.info(@json(session('info')));
        @endif
        
        @if($errors->any())
        if (typeof window.Popup !== 'undefined') {
            window.Popup.error('Vui lòng kiểm tra lại', '<ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>');
        }
        @endif
    }
    
    tryShowToast();
})();
</script>
@endif
