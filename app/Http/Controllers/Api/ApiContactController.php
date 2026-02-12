<?php

namespace App\Http\Controllers\Api;

use App\Models\ContactMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ApiContactController extends ApiController
{
    /**
     * Store contact message
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:2000'],
        ], [
            'name.required' => 'Vui lòng nhập họ tên',
            'email.required' => 'Vui lòng nhập email',
            'email.email' => 'Email không hợp lệ',
            'subject.required' => 'Vui lòng nhập chủ đề',
            'message.required' => 'Vui lòng nhập nội dung',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            $contact = ContactMessage::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'subject' => $request->subject,
                'message' => $request->message,
                'is_read' => false,
            ]);

            return $this->successResponse([
                'id' => $contact->id,
                'created_at' => $contact->created_at->toIso8601String(),
            ], 'Gửi tin nhắn thành công. Chúng tôi sẽ phản hồi trong thời gian sớm nhất.', 201);

        } catch (\Exception $e) {
            return $this->serverErrorResponse('Có lỗi xảy ra khi gửi tin nhắn: ' . $e->getMessage());
        }
    }
}
