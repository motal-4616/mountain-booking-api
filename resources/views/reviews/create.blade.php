@extends('layouts.app')

@section('title', 'Đánh giá Tour - ' . $tour->name)

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Tour Info Card -->
            <div class="card-modern mb-4">
                <div class="row g-0">
                    <div class="col-md-4">
                        <img src="{{ asset($tour->image) }}" 
                             alt="{{ $tour->name }}" 
                             class="img-fluid rounded-start h-100" 
                             style="object-fit: cover;">
                    </div>
                    <div class="col-md-8">
                        <div class="card-body">
                            <h5 class="card-title fw-bold">{{ $tour->name }}</h5>
                            <p class="text-muted mb-2">
                                <i class="bi bi-geo-alt me-1"></i>{{ $tour->location }}
                            </p>
                            @if(isset($booking))
                            <p class="text-muted mb-2">
                                <i class="bi bi-calendar me-1"></i>
                                Ngày đi: {{ \Carbon\Carbon::parse($booking->schedule->departure_date)->format('d/m/Y') }}
                                @if($booking->schedule->end_date)
                                - {{ \Carbon\Carbon::parse($booking->schedule->end_date)->format('d/m/Y') }}
                                @endif
                            </p>
                            @endif
                            <div class="d-flex gap-2">
                                <span class="badge-modern badge-{{ $tour->difficulty === 'easy' ? 'success' : ($tour->difficulty === 'medium' ? 'warning' : 'danger') }}">
                                    {{ $tour->difficulty_text }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Review Form -->
            <div class="card-modern">
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <h3 class="fw-bold mb-2">Đánh giá trải nghiệm của bạn</h3>
                        <p class="text-muted">Chia sẻ cảm nhận để giúp người khác có thêm thông tin</p>
                    </div>

                    <form method="POST" action="{{ route('reviews.store') }}">
                        @csrf
                        <input type="hidden" name="tour_id" value="{{ $tour->id }}">
                        @if(isset($booking))
                        <input type="hidden" name="booking_id" value="{{ $booking->id }}">
                        @endif

                        <!-- Rating Stars -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold mb-3">
                                Đánh giá của bạn <span class="text-danger">*</span>
                            </label>
                            <div class="star-rating-input d-flex gap-2 justify-content-center">
                                <input type="radio" name="rating" value="1" id="star1" required>
                                <label for="star1" class="star-label">
                                    <i class="bi bi-star-fill"></i>
                                </label>
                                
                                <input type="radio" name="rating" value="2" id="star2">
                                <label for="star2" class="star-label">
                                    <i class="bi bi-star-fill"></i>
                                </label>
                                
                                <input type="radio" name="rating" value="3" id="star3">
                                <label for="star3" class="star-label">
                                    <i class="bi bi-star-fill"></i>
                                </label>
                                
                                <input type="radio" name="rating" value="4" id="star4">
                                <label for="star4" class="star-label">
                                    <i class="bi bi-star-fill"></i>
                                </label>
                                
                                <input type="radio" name="rating" value="5" id="star5">
                                <label for="star5" class="star-label">
                                    <i class="bi bi-star-fill"></i>
                                </label>
                            </div>
                            <div class="text-center mt-2">
                                <small class="text-muted rating-text">Chọn số sao để đánh giá</small>
                            </div>
                            @error('rating')
                                <div class="text-danger small mt-2 text-center">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Title -->
                        <div class="mb-3">
                            <label for="title" class="form-label fw-semibold">
                                Tiêu đề đánh giá
                            </label>
                            <input type="text" 
                                   class="form-control form-control-modern @error('title') is-invalid @enderror" 
                                   id="title" 
                                   name="title"
                                   value="{{ old('title') }}"
                                   placeholder="Ví dụ: Trải nghiệm tuyệt vời!"
                                   maxlength="255">
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Comment -->
                        <div class="mb-4">
                            <label for="comment" class="form-label fw-semibold">
                                Nhận xét chi tiết
                            </label>
                            <textarea class="form-control form-control-modern @error('comment') is-invalid @enderror" 
                                      id="comment" 
                                      name="comment" 
                                      rows="6"
                                      placeholder="Chia sẻ chi tiết về trải nghiệm của bạn với tour này..."
                                      maxlength="1000">{{ old('comment') }}</textarea>
                            <div class="form-text">Tối đa 1000 ký tự</div>
                            @error('comment')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Buttons -->
                        <div class="d-flex gap-2 justify-content-end">
                            @if(isset($booking))
                            <a href="{{ route('bookings.show', $booking->id) }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle me-2"></i>Hủy
                            </a>
                            @else
                            <a href="{{ route('tours.show', $tour->id) }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle me-2"></i>Hủy
                            </a>
                            @endif
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-send me-2"></i>Gửi đánh giá
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Star Rating Styles */
.star-rating-input {
    flex-direction: row-reverse;
    font-size: 48px;
}

.star-rating-input input[type="radio"] {
    display: none;
}

.star-label {
    cursor: pointer;
    color: #e2e8f0;
    transition: all 0.2s ease;
    margin: 0;
}

.star-label:hover,
.star-label:hover ~ .star-label,
.star-rating-input input[type="radio"]:checked ~ .star-label {
    color: #fbbf24;
}

.star-label:hover {
    transform: scale(1.1);
}

.rating-text {
    font-size: 14px;
}

/* Update rating text based on selection */
.star-rating-input input[type="radio"]:checked ~ .rating-text {
    font-weight: 600;
    color: #fbbf24 !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ratingInputs = document.querySelectorAll('.star-rating-input input[type="radio"]');
    const ratingText = document.querySelector('.rating-text');
    
    const ratingTexts = {
        1: '⭐ Rất tệ',
        2: '⭐⭐ Tệ',
        3: '⭐⭐⭐ Bình thường',
        4: '⭐⭐⭐⭐ Tốt',
        5: '⭐⭐⭐⭐⭐ Tuyệt vời'
    };
    
    ratingInputs.forEach(input => {
        input.addEventListener('change', function() {
            if (this.checked) {
                ratingText.textContent = ratingTexts[this.value];
            }
        });
    });
});
</script>
@endsection
