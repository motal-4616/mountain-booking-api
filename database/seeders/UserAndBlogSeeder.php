<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\BlogPost;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class UserAndBlogSeeder extends Seeder
{
    public function run(): void
    {
        // ===== TẠO THÊM NGƯỜI DÙNG =====
        $users = [
            [
                'name' => 'Trần Minh Khoa',
                'email' => 'khoa.tran@gmail.com',
                'phone' => '0912345678',
                'password' => Hash::make('123456'),
                'role' => 'user',
                'bio' => 'Yêu thiên nhiên, thích chinh phục đỉnh cao. Đã leo hơn 20 ngọn núi Việt Nam.',
            ],
            [
                'name' => 'Lê Thị Hương',
                'email' => 'huong.le@gmail.com',
                'phone' => '0923456789',
                'password' => Hash::make('123456'),
                'role' => 'user',
                'bio' => 'Travel blogger, chia sẻ hành trình khám phá núi rừng Tây Bắc.',
            ],
            [
                'name' => 'Phạm Đức Anh',
                'email' => 'ducanh.pham@gmail.com',
                'phone' => '0934567890',
                'password' => Hash::make('123456'),
                'role' => 'user',
                'bio' => 'Hướng dẫn viên leo núi chuyên nghiệp, 5 năm kinh nghiệm.',
            ],
            [
                'name' => 'Nguyễn Thùy Linh',
                'email' => 'linh.nguyen@gmail.com',
                'phone' => '0945678901',
                'password' => Hash::make('123456'),
                'role' => 'user',
                'bio' => 'Newbie leo núi, đang tập luyện để chinh phục Fansipan!',
            ],
            [
                'name' => 'Hoàng Văn Tuấn',
                'email' => 'tuan.hoang@gmail.com',
                'phone' => '0956789012',
                'password' => Hash::make('123456'),
                'role' => 'user',
                'bio' => 'Photographer chuyên chụp ảnh phong cảnh núi rừng.',
            ],
            [
                'name' => 'Đỗ Thanh Mai',
                'email' => 'mai.do@gmail.com',
                'phone' => '0967890123',
                'password' => Hash::make('123456'),
                'role' => 'user',
                'bio' => 'Bác sĩ thể thao, chia sẻ kiến thức sức khỏe khi leo núi.',
            ],
            [
                'name' => 'Vũ Quang Huy',
                'email' => 'huy.vu@gmail.com',
                'phone' => '0978901234',
                'password' => Hash::make('123456'),
                'role' => 'user',
                'bio' => 'Đam mê camping, trekking và khám phá thiên nhiên hoang dã.',
            ],
            [
                'name' => 'Bùi Ngọc Hà',
                'email' => 'ha.bui@gmail.com',
                'phone' => '0989012345',
                'password' => Hash::make('123456'),
                'role' => 'user',
                'bio' => 'Sinh viên năm cuối, thích phượt và leo núi cuối tuần.',
            ],
            [
                'name' => 'Trịnh Công Sơn',
                'email' => 'son.trinh@gmail.com',
                'phone' => '0990123456',
                'password' => Hash::make('123456'),
                'role' => 'user',
                'bio' => 'Runner chuyển sang leo núi, yêu thích các thử thách outdoor.',
            ],
            [
                'name' => 'Lý Thị Bích Ngọc',
                'email' => 'ngoc.ly@gmail.com',
                'phone' => '0901234567',
                'password' => Hash::make('123456'),
                'role' => 'user',
                'bio' => 'Food blogger kết hợp du lịch leo núi, chia sẻ ẩm thực vùng cao.',
            ],
        ];

        $createdUsers = [];
        foreach ($users as $userData) {
            $createdUsers[] = User::create($userData);
        }

        // ===== TẠO BLOG POSTS =====
        $blogPosts = [
            // ---- GUIDE (Hướng dẫn) ----
            [
                'title' => 'Hướng dẫn chinh phục Fansipan cho người mới bắt đầu',
                'content' => "Fansipan - nóc nhà Đông Dương với độ cao 3.143m, là ước mơ của mọi người yêu leo núi.\n\n## Chuẩn bị trước chuyến đi\n\nĐể chinh phục Fansipan, bạn cần chuẩn bị kỹ lưỡng:\n\n### Thể lực\n- Tập chạy bộ ít nhất 1 tháng trước\n- Tập leo cầu thang 20-30 tầng mỗi ngày\n- Tập plank và squat để tăng sức bền chân\n\n### Đồ dùng cần thiết\n- Giày trekking chống trượt\n- Ba lô 30-40L\n- Áo khoác chống gió, chống nước\n- Đèn pin đội đầu\n- Gậy trekking (rất quan trọng!)\n\n### Lộ trình phổ biến\n1. **Ngày 1**: Trạm Tôn → Trạm 2100m (5-6 tiếng)\n2. **Ngày 2**: Trạm 2100m → Đỉnh Fansipan → Xuống núi\n\n## Lưu ý quan trọng\n- Luôn đi theo nhóm, không tách đoàn\n- Uống đủ nước, ít nhất 2-3 lít/ngày\n- Nghỉ ngơi khi cảm thấy mệt, không gắng sức",
                'excerpt' => 'Tất cả những gì bạn cần biết để chuẩn bị cho chuyến chinh phục nóc nhà Đông Dương - Fansipan 3.143m.',
                'category' => 'guide',
                'tags' => ['fansipan', 'beginner', 'trekking', 'lào cai'],
                'status' => 'published',
                'is_featured' => true,
                'view_count' => 2456,
                'likes_count' => 189,
                'comments_count' => 45,
            ],
            [
                'title' => 'Cẩm nang leo núi Tà Năng - Phan Dũng: Cung đường trekking đẹp nhất Việt Nam',
                'content' => "Tà Năng - Phan Dũng được mệnh danh là cung trekking đẹp nhất Việt Nam với chiều dài khoảng 55km.\n\n## Giới thiệu cung đường\n\nCung đường đi qua 3 tỉnh: Lâm Đồng - Ninh Thuận - Bình Thuận, xuyên qua đồi cỏ, rừng già và suối.\n\n## Lịch trình chi tiết\n\n### Ngày 1: Tà Năng → Bãi cỏ giữa rừng\n- Xuất phát từ xã Tà Năng, huyện Đức Trọng\n- Đi qua cánh đồng cỏ rộng bao la\n- Dựng trại nghỉ đêm tại bãi cỏ\n\n### Ngày 2: Bãi cỏ → Suối → Phan Dũng\n- Vượt suối, leo dốc xuyên rừng\n- Đến thác nước tuyệt đẹp\n- Kết thúc tại Phan Dũng\n\n## Chi phí tham khảo\n- Xe cộ: 500.000đ - 800.000đ\n- Porter + dẫn đường: 300.000đ/ngày\n- Ăn uống: 200.000đ/ngày",
                'excerpt' => 'Khám phá cung đường trekking 55km xuyên 3 tỉnh, đi qua đồi cỏ bao la và rừng nguyên sinh.',
                'category' => 'guide',
                'tags' => ['tà năng', 'phan dũng', 'trekking', 'lâm đồng'],
                'status' => 'published',
                'is_featured' => false,
                'view_count' => 1823,
                'likes_count' => 134,
                'comments_count' => 32,
            ],
            [
                'title' => 'Hướng dẫn sử dụng la bàn và bản đồ khi leo núi',
                'content' => "Trong thời đại GPS, nhiều người quên kỹ năng đọc bản đồ và la bàn. Nhưng đây là kỹ năng sống còn khi mất sóng điện thoại.\n\n## Cách đọc la bàn\n\n### Các hướng cơ bản\n- **N (North)**: Bắc\n- **S (South)**: Nam\n- **E (East)**: Đông\n- **W (West)**: Tây\n\n### Cách xác định phương hướng\n1. Đặt la bàn trên mặt phẳng\n2. Kim từ luôn chỉ hướng Bắc\n3. Xoay thân la bàn cho chữ N trùng kim\n4. Xác định hướng cần đi\n\n## Đọc bản đồ địa hình\n- Đường đồng mức càng gần nhau = dốc càng đứng\n- Đường đồng mức thưa = địa hình thoải\n- Chú ý ký hiệu suối, đường mòn, điểm cao\n\n## Kết hợp la bàn + bản đồ\n1. Xác định vị trí hiện tại trên bản đồ\n2. Tìm điểm đích\n3. Xác định góc phương vị\n4. Đi theo hướng la bàn chỉ",
                'excerpt' => 'Kỹ năng sinh tồn quan trọng nhất khi leo núi - cách sử dụng la bàn và đọc bản đồ địa hình.',
                'category' => 'guide',
                'tags' => ['la bàn', 'bản đồ', 'kỹ năng', 'sinh tồn'],
                'status' => 'published',
                'is_featured' => false,
                'view_count' => 987,
                'likes_count' => 76,
                'comments_count' => 18,
            ],
            [
                'title' => 'Hướng dẫn chọn lều cắm trại phù hợp cho leo núi',
                'content' => "Chọn lều đúng là yếu tố quyết định chất lượng giấc ngủ và sự an toàn khi cắm trại trên núi.\n\n## Phân loại lều\n\n### Theo số mùa\n- **Lều 3 mùa**: Phù hợp Xuân-Hè-Thu, nhẹ, thoáng\n- **Lều 4 mùa**: Chịu được gió mạnh, tuyết, nặng hơn\n\n### Theo số người\n- **1 người**: 1.5-2kg, phù hợp solo\n- **2 người**: 2-3kg, phổ biến nhất\n- **3-4 người**: 3-5kg, cho nhóm\n\n## Top lều được khuyến nghị\n1. **Naturehike Cloud Up 2**: Giá tốt, nhẹ 1.8kg\n2. **MSR Hubba Hubba NX**: Cao cấp, siêu bền\n3. **Big Agnes Copper Spur**: Rộng rãi, thoáng mát\n\n## Mẹo cắm lều\n- Chọn nền đất bằng, không có đá\n- Hướng cửa tránh gió\n- Đặt tấm lót dưới sàn lều\n- Cố định dây chằng kỹ lưỡng",
                'excerpt' => 'Tổng hợp kinh nghiệm chọn lều cắm trại từ bình dân đến cao cấp, phù hợp cho leo núi Việt Nam.',
                'category' => 'guide',
                'tags' => ['lều', 'camping', 'trang bị', 'review'],
                'status' => 'published',
                'is_featured' => false,
                'view_count' => 1245,
                'likes_count' => 98,
                'comments_count' => 22,
            ],

            // ---- TIPS (Mẹo) ----
            [
                'title' => '10 mẹo giữ ấm cơ thể khi leo núi mùa đông',
                'content' => "Mùa đông là thời điểm tuyệt vời để leo núi nhưng cũng đầy thách thức về nhiệt độ.\n\n## 10 Mẹo giữ ấm hiệu quả\n\n### 1. Mặc lớp (Layering System)\n- **Lớp trong (Base layer)**: Vải nhanh khô, không cotton\n- **Lớp giữa (Mid layer)**: Fleece giữ nhiệt\n- **Lớp ngoài (Shell)**: Chống gió, chống nước\n\n### 2. Bảo vệ đầu và cổ\nMất 40% thân nhiệt qua đầu. Luôn đội mũ len và quấn khăn.\n\n### 3. Giữ ấm tay chân\nDùng găng tay 2 lớp và tất len merino.\n\n### 4. Ăn đồ ăn nóng\nMang theo bình giữ nhiệt chứa nước nóng hoặc trà gừng.\n\n### 5. Vận động liên tục\nKhông đứng yên quá lâu, di chuyển nhẹ nhàng để sinh nhiệt.\n\n### 6. Túi ngủ đúng chuẩn\nChọn túi ngủ comfort rating thấp hơn nhiệt độ dự kiến 5°C.\n\n### 7. Lót giày bằng lá khô\nMẹo dân gian nhưng hiệu quả bất ngờ!\n\n### 8. Ăn nhiều calo\nCơ thể cần nhiều năng lượng hơn để giữ ấm.\n\n### 9. Tránh đổ mồ hôi\nĐiều chỉnh tốc độ đi, cởi bớt lớp khi nóng.\n\n### 10. Miếng dán giữ nhiệt\nDán vào lưng, bụng hoặc trong giày.",
                'excerpt' => 'Tổng hợp 10 mẹo đơn giản nhưng hiệu quả để giữ ấm cơ thể khi leo núi trong mùa lạnh.',
                'category' => 'tips',
                'tags' => ['mùa đông', 'giữ ấm', 'mẹo', 'trang bị'],
                'status' => 'published',
                'is_featured' => false,
                'view_count' => 3210,
                'likes_count' => 267,
                'comments_count' => 58,
            ],
            [
                'title' => 'Cách xử lý khi bị lạc đường trong rừng',
                'content' => "Bị lạc đường là tình huống nguy hiểm nhất khi đi rừng. Hãy ghi nhớ nguyên tắc STOP.\n\n## Nguyên tắc STOP\n\n### S - Sit (Ngồi xuống)\nĐừng hoảng loạn, bình tĩnh ngồi xuống suy nghĩ.\n\n### T - Think (Suy nghĩ)\nNhớ lại lần cuối biết chính xác vị trí. Kiểm tra bản đồ, la bàn.\n\n### O - Observe (Quan sát)\n- Tìm dấu hiệu quen thuộc\n- Nhìn cây cối, hướng nắng\n- Lắng nghe tiếng suối, xe cộ\n\n### P - Plan (Lên kế hoạch)\n- Nếu có thể quay lại → quay lại\n- Nếu không → ở yên một chỗ chờ cứu hộ\n\n## Tín hiệu cầu cứu\n- Thổi còi 3 tiếng liên tục\n- Đốt lửa tạo khói\n- Xếp đá/cành cây thành chữ SOS\n- Dùng gương phản chiếu ánh sáng\n\n## Tìm nước\n- Đi theo hướng xuống dốc\n- Tìm theo tiếng suối chảy\n- Sương đọng trên lá cây buổi sáng",
                'excerpt' => 'Kỹ năng sinh tồn quan trọng: nguyên tắc STOP và cách phát tín hiệu cầu cứu khi bị lạc trong rừng.',
                'category' => 'tips',
                'tags' => ['sinh tồn', 'lạc đường', 'an toàn', 'kỹ năng'],
                'status' => 'published',
                'is_featured' => true,
                'view_count' => 4521,
                'likes_count' => 345,
                'comments_count' => 72,
            ],
            [
                'title' => 'Chế độ dinh dưỡng tối ưu cho 3 ngày trekking',
                'content' => "Ăn gì, mang gì để đảm bảo đủ năng lượng cho chuyến trekking dài ngày?\n\n## Nguyên tắc chung\n- Cần 3000-4000 calo/ngày khi trekking\n- Tỷ lệ: 60% carb, 25% chất béo, 15% protein\n- Uống 3-4 lít nước/ngày\n\n## Gợi ý thực đơn\n\n### Bữa sáng\n- Yến mạch trộn hạt mixed + mật ong\n- Bánh mì năng lượng + bơ đậu phộng\n- Chuối + thanh energy bar\n\n### Bữa trưa (ăn nhanh)\n- Mì ăn liền + trứng luộc\n- Cơm nắm + ruốc/muối mè\n- Bánh tráng + khô bò\n\n### Bữa tối\n- Cơm + thịt hộp + rau rừng\n- Cháo gà ăn liền\n- Mì Ý đóng gói\n\n### Snack dọc đường\n- Hạt điều, hạnh nhân\n- Kẹo sô-cô-la\n- Trái cây sấy khô\n- Thanh protein bar\n\n## Thực phẩm nên tránh\n- Đồ ăn dễ hỏng (sữa tươi, hải sản)\n- Đồ ăn quá mặn\n- Rượu bia",
                'excerpt' => 'Kế hoạch dinh dưỡng chi tiết cho 3 ngày trekking, đảm bảo đủ năng lượng mà vẫn nhẹ ba lô.',
                'category' => 'tips',
                'tags' => ['dinh dưỡng', 'thực phẩm', 'trekking', 'sức khỏe'],
                'status' => 'published',
                'is_featured' => false,
                'view_count' => 1876,
                'likes_count' => 156,
                'comments_count' => 34,
            ],
            [
                'title' => '5 bài tập thể lực chuẩn bị cho chuyến leo núi',
                'content' => "Không cần phải là vận động viên để leo núi, chỉ cần tập luyện đúng cách trong 4-6 tuần.\n\n## 5 bài tập hiệu quả nhất\n\n### 1. Squat (3x15 reps)\nTăng sức mạnh đùi và mông, giúp leo dốc dễ dàng hơn.\n\n### 2. Lunges (3x12 mỗi chân)\nMô phỏng động tác bước lên bậc thang núi.\n\n### 3. Step-ups (3x15 mỗi chân)\nDùng ghế cao 40-50cm, bước lên xuống liên tục.\n\n### 4. Plank (3x60 giây)\nCore mạnh giúp giữ thăng bằng khi mang ba lô nặng.\n\n### 5. Chạy bộ/Đạp xe (30-45 phút)\nTăng sức bền tim phổi, cardio là quan trọng nhất.\n\n## Lịch tập gợi ý\n- **Thứ 2, 4, 6**: Bài tập sức mạnh + cardio\n- **Thứ 3, 5**: Chạy bộ nhẹ hoặc đi bộ nhanh\n- **Thứ 7**: Leo cầu thang tòa nhà 15-20 tầng\n- **Chủ nhật**: Nghỉ ngơi\n\n## Lưu ý\n- Tập với ba lô có tải trọng tăng dần\n- Không tập quá sức, nghỉ ngơi đủ\n- Kéo giãn cơ sau mỗi buổi tập",
                'excerpt' => 'Chương trình tập luyện 6 tuần với 5 bài tập đơn giản, giúp bạn sẵn sàng cho mọi chuyến leo núi.',
                'category' => 'tips',
                'tags' => ['tập luyện', 'thể lực', 'chuẩn bị', 'sức khỏe'],
                'status' => 'published',
                'is_featured' => false,
                'view_count' => 2134,
                'likes_count' => 178,
                'comments_count' => 41,
            ],

            // ---- REVIEWS (Đánh giá) ----
            [
                'title' => 'Review chi tiết tour leo Bạch Mộc Lương Tử 2 ngày 1 đêm',
                'content' => "Bạch Mộc Lương Tử (hay Ky Quan San) cao 3.046m, đỉnh núi cao thứ 4 Việt Nam.\n\n## Thông tin tour\n- **Đơn vị tổ chức**: Mountain Booking\n- **Thời gian**: 2 ngày 1 đêm\n- **Giá**: 2.500.000đ/người\n- **Độ khó**: 7/10\n\n## Ngày 1: Xuất phát\nXuất phát lúc 5h sáng từ Sapa. Xe đưa đến chân núi mất 2 tiếng. Bắt đầu leo từ 8h.\n\nĐường đi khá khó, nhiều đoạn dốc đứng và phải bám dây thừng. Nhưng cảnh đẹp mê hồn!\n\nĐến lều trại lúc 16h, cắm trại ở độ cao 2.800m. Trời lạnh khoảng 5°C.\n\n## Ngày 2: Chinh phục đỉnh\nDậy lúc 4h để xem bình minh. Leo thêm 2 tiếng để lên đỉnh.\n\nĐứng trên đỉnh Bạch Mộc nhìn ra biển mây - cảm giác không thể diễn tả bằng lời!\n\n## Đánh giá\n- **Cảnh đẹp**: ⭐⭐⭐⭐⭐ (10/10)\n- **Độ khó**: ⭐⭐⭐⭐ (7/10)\n- **Dịch vụ**: ⭐⭐⭐⭐ (8/10)\n- **Giá cả**: ⭐⭐⭐⭐ (8/10)\n\n**Kết luận**: Rất đáng để thử, nhưng cần có thể lực tốt!",
                'excerpt' => 'Đánh giá chi tiết tour chinh phục Bạch Mộc Lương Tử - đỉnh núi cao thứ 4 Việt Nam với cung đường đầy thách thức.',
                'category' => 'reviews',
                'tags' => ['review', 'bạch mộc lương tử', 'sapa', 'tour'],
                'status' => 'published',
                'is_featured' => false,
                'view_count' => 1567,
                'likes_count' => 123,
                'comments_count' => 28,
            ],
            [
                'title' => 'So sánh 3 đôi giày trekking phổ biến nhất 2025',
                'content' => "Sau khi sử dụng 3 đôi giày phổ biến trong suốt 1 năm, đây là đánh giá chi tiết.\n\n## 1. Salomon X Ultra 4 GTX\n- **Giá**: 3.500.000đ\n- **Cân nặng**: 890g/đôi\n- **Ưu điểm**: Nhẹ, grip tốt, chống nước hoàn hảo\n- **Nhược điểm**: Đế hơi cứng lúc đầu\n- **Đánh giá**: 9/10\n\n## 2. Merrell Moab 3\n- **Giá**: 2.800.000đ\n- **Cân nặng**: 960g/đôi\n- **Ưu điểm**: Thoải mái ngay từ đầu, giá tốt\n- **Nhược điểm**: Chống nước kém hơn, mau mòn\n- **Đánh giá**: 7.5/10\n\n## 3. The North Face Vectiv Exploris 2\n- **Giá**: 4.200.000đ\n- **Cân nặng**: 850g/đôi\n- **Ưu điểm**: Siêu nhẹ, đệm tuyệt vời\n- **Nhược điểm**: Giá cao, ít cửa hàng bán\n- **Đánh giá**: 8.5/10\n\n## Kết luận\n- **Tổng thể tốt nhất**: Salomon X Ultra 4\n- **Giá tốt nhất**: Merrell Moab 3\n- **Nhẹ nhất**: TNF Vectiv Exploris 2",
                'excerpt' => 'So sánh trực tiếp Salomon X Ultra 4, Merrell Moab 3 và TNF Vectiv Exploris 2 sau 1 năm sử dụng.',
                'category' => 'reviews',
                'tags' => ['review', 'giày trekking', 'trang bị', 'so sánh'],
                'status' => 'published',
                'is_featured' => false,
                'view_count' => 5678,
                'likes_count' => 412,
                'comments_count' => 89,
            ],
            [
                'title' => 'Review bếp gas mini Kovea Spider: Có xứng đáng phân khúc cao cấp?',
                'content' => "Kovea Spider là bếp gas mini được nhiều dân trekking chuyên nghiệp tin dùng.\n\n## Thông số kỹ thuật\n- **Cân nặng**: 187g\n- **Công suất**: 1800W\n- **Nhiên liệu**: Gas lon Butane/Propane\n- **Giá**: 1.200.000đ\n\n## Trải nghiệm thực tế\n\n### Đun nước\nĐun sôi 1 lít nước trong 4 phút - khá nhanh!\n\n### Nấu ăn\nLửa đều, chỉnh được nhỏ lửa. Nấu mì, xào rau đều OK.\n\n### Trong gió\nThiết kế chân nhện giúp đặt vững. Cần tấm chắn gió bổ sung.\n\n## Ưu điểm\n- Gập gọn, nhẹ\n- Chất lượng gia công tốt\n- Lửa mạnh, đều\n\n## Nhược điểm\n- Giá cao so với bếp TQ\n- Không có piezo (cần bật lửa)\n\n**Đánh giá: 8.5/10** - Đáng đồng tiền cho ai leo núi thường xuyên.",
                'excerpt' => 'Đánh giá chi tiết bếp gas mini Kovea Spider sau 6 tháng sử dụng trên nhiều cung trekking.',
                'category' => 'reviews',
                'tags' => ['review', 'bếp gas', 'trang bị', 'camping'],
                'status' => 'published',
                'is_featured' => false,
                'view_count' => 890,
                'likes_count' => 67,
                'comments_count' => 15,
            ],

            // ---- STORIES (Câu chuyện) ----
            [
                'title' => 'Đêm mưa bão trên đỉnh Pu Ta Leng - Ký ức không bao giờ quên',
                'content' => "Pu Ta Leng, đỉnh núi cao thứ 5 Việt Nam với 3.049m. Chuyến đi năm ngoái suýt thành thảm họa.\n\n## Ngày thứ 2 - Cơn bão bất ngờ\n\nChúng tôi đang ở độ cao 2.700m khi mây đen kéo đến. Mưa bắt đầu từ 14h và không dừng lại.\n\nGió mạnh đến mức lều suýt bay. 3 người co cụm trong 1 lều 2 người. Nhiệt độ giảm xuống 2°C.\n\nTiếng sấm vang rền qua đỉnh núi. Lần đầu tiên tôi hiểu thế nào là sợ hãi thực sự.\n\n## Bài học rút ra\n\n1. **Luôn check thời tiết** trước và trong chuyến đi\n2. **Mang đủ áo mưa** cho mọi tình huống\n3. **Biết cách cắm lều** chống gió đúng cách\n4. **Tinh thần đoàn kết** giúp vượt qua mọi khó khăn\n\nSáng hôm sau, khi mây tan, chúng tôi được thưởng biển mây đẹp nhất đời. Có lẽ thiên nhiên muốn thử thách trước khi ban tặng.\n\n*Đôi khi, những khoảnh khắc khó khăn nhất lại tạo nên ký ức đẹp nhất.*",
                'excerpt' => 'Câu chuyện về đêm mưa bão kinh hoàng trên đỉnh Pu Ta Leng và bài học sinh tồn đáng nhớ.',
                'category' => 'stories',
                'tags' => ['pu ta leng', 'câu chuyện', 'kinh nghiệm', 'mưa bão'],
                'status' => 'published',
                'is_featured' => false,
                'view_count' => 3456,
                'likes_count' => 289,
                'comments_count' => 67,
            ],
            [
                'title' => 'Lần đầu leo núi của cô gái 50kg - Hành trình chinh phục Lảo Thẩn',
                'content' => "Tôi, 25 tuổi, nặng 50kg, chưa bao giờ leo núi. Và tôi quyết định chinh phục Lảo Thẩn 2.860m.\n\n## Quyết định điên rồ\n\nBạn bè bảo tôi điên. Gia đình lo lắng. Nhưng tôi muốn chứng minh rằng ai cũng có thể làm được.\n\n## Chuẩn bị 2 tháng\n- Chạy bộ 5km mỗi sáng\n- Tập leo cầu thang với ba lô 8kg\n- Đọc mọi thứ về Lảo Thẩn\n\n## Hành trình\n\n### 5h sáng - Xuất phát\nTim đập thình thịch. Nhìn đỉnh núi mờ trong sương mà muốn bỏ cuộc ngay.\n\n### 10h - Đoạn dốc đứng\nChân run, tay bám đá. Mồ hôi ướt đẫm. 'Mình không thể bỏ cuộc' - tôi tự nhủ.\n\n### 14h - ĐỈNH!!!\nKhóc. Khóc thật sự. Đứng trên đỉnh Lảo Thẩn nhìn xuống biển mây mà nước mắt cứ rơi.\n\nKhoảnh khắc đó, tôi hiểu rằng giới hạn chỉ tồn tại trong đầu mình.\n\n## Thông điệp\n*Bạn không cần phải mạnh mẽ để bắt đầu. Bạn chỉ cần bắt đầu để trở nên mạnh mẽ.*",
                'excerpt' => 'Hành trình đầy cảm xúc của cô gái 50kg lần đầu leo núi, chinh phục đỉnh Lảo Thẩn 2.860m.',
                'category' => 'stories',
                'tags' => ['lảo thẩn', 'câu chuyện', 'lần đầu', 'cảm hứng'],
                'status' => 'published',
                'is_featured' => true,
                'view_count' => 7823,
                'likes_count' => 645,
                'comments_count' => 134,
            ],
            [
                'title' => 'Gặp gỡ bà cụ 70 tuổi vẫn porter trên núi',
                'content' => "Trên cung đường Tà Xùa, chúng tôi gặp bà Mùa, 70 tuổi, người H'Mông, vẫn mang hàng lên núi mỗi ngày.\n\n## Cuộc gặp gỡ\n\nBà đang gánh 2 bao ngô nặng ít nhất 30kg, đi trên con đường mà chúng tôi phải thở hổn hển với ba lô 10kg.\n\n'Quen rồi cháu ơi, đi từ nhỏ mà' - bà cười, răng nhuộm đen.\n\n## Câu chuyện của bà\n\nBà sinh ra trên núi, lớn lên trên núi. Chồng mất sớm, một mình nuôi 5 đứa con. Mỗi ngày gánh hàng xuống chợ bán, chiều lại gánh đồ về.\n\n'Núi là nhà, đường là bạn. Không đi thì buồn lắm.'\n\n## Suy ngẫm\n\nChúng tôi leo núi để giải trí, để thử thách bản thân. Nhưng với bà Mùa, núi là cuộc sống, là cơm áo.\n\nSau hôm đó, mỗi khi muốn bỏ cuộc giữa đường, tôi lại nhớ đến nụ cười của bà Mùa.\n\n*Có những người không chọn núi, mà núi đã chọn họ.*",
                'excerpt' => 'Câu chuyện cảm động về bà cụ 70 tuổi người H\'Mông vẫn gánh hàng qua các đỉnh núi mỗi ngày.',
                'category' => 'stories',
                'tags' => ['câu chuyện', 'con người', 'tà xùa', 'cảm hứng'],
                'status' => 'published',
                'is_featured' => false,
                'view_count' => 6234,
                'likes_count' => 534,
                'comments_count' => 98,
            ],
            [
                'title' => 'Solo trekking Tà Xùa mùa mây - 3 ngày đáng nhớ nhất đời',
                'content' => "Một mình, một ba lô, 3 ngày trên đỉnh Tà Xùa giữa mùa mây (tháng 12-3).\n\n## Tại sao solo?\n\nĐôi khi bạn cần ở một mình để lắng nghe chính mình. Và không có nơi nào tốt hơn đỉnh núi.\n\n## Ngày 1 - Khởi đầu\nĐến bản Háng Đồng lúc chiều. Homestay của anh Giàng, 200k/đêm. Ăn xôi ngũ sắc với thịt gác bếp. Ngủ sớm để 4h dậy.\n\n## Ngày 2 - Săn mây\n4h sáng, trời tối đen. Bật đèn pin leo lên đỉnh 1. Lạnh cắt da, nhưng khi bình minh lên và biển mây trải ra trước mắt - mọi mệt mõi tan biến.\n\nNgồi trên đỉnh núi 3 tiếng, một mình giữa biển mây. Im lặng. Thanh bình.\n\n## Ngày 3 - Lưu luyến\nĐi sang đỉnh 2 và đỉnh 3. Đường khó hơn, phải bám dây thừng. Nhưng đỉnh 3 (sống lưng khủng long) mới là highlight.\n\nĐứng trên sống núi hẹp, hai bên là vực sâu ngàn mét. Tim đập loạn, nhưng cảm giác tự do tuyệt đối.\n\n*Solo không có nghĩa là cô đơn. Đôi khi đó là cách gần nhất để gặp lại chính mình.*",
                'excerpt' => '3 ngày solo trekking Tà Xùa giữa mùa mây - hành trình tự khám phá bản thân trên đỉnh trời.',
                'category' => 'stories',
                'tags' => ['tà xùa', 'solo', 'săn mây', 'trekking'],
                'status' => 'published',
                'is_featured' => false,
                'view_count' => 4567,
                'likes_count' => 378,
                'comments_count' => 82,
            ],
            [
                'title' => 'Mùa hoa đỗ quyên trên đỉnh Putaleng - Thiên đường có thật',
                'content' => "Mỗi năm vào tháng 3-4, đỉnh Putaleng bừng sáng với hàng ngàn cây đỗ quyên nở rộ.\n\n## Thời điểm vàng\n- **Tháng 3**: Hoa bắt đầu nở, ít người\n- **Đầu tháng 4**: Nở rộ nhất, rất đông\n- **Cuối tháng 4**: Hoa tàn dần\n\n## Hành trình\n\nXuất phát từ bản Phô, xã Hồ Thầu, Tam Đường, Lai Châu. Leo khoảng 8 tiếng để lên đến vùng hoa.\n\nKhi lên đến độ cao 2.500m, rừng đỗ quyên bắt đầu xuất hiện. Hoa đỏ, hồng, trắng phủ kín cả sườn núi.\n\n## Vẻ đẹp không lời\n\nẢnh không thể diễn tả được vẻ đẹp thực. Những cây đỗ quyên cổ thụ hàng trăm năm tuổi, cao 5-10m, phủ đầy hoa.\n\nSương sớm đọng trên cánh hoa, ánh nắng xuyên qua tán lá tạo nên khung cảnh như cổ tích.\n\n## Tips chụp ảnh\n- Đi sáng sớm khi còn sương\n- Dùng góc wide để bao trọn không gian\n- Chụp chi tiết giọt sương trên hoa\n- Golden hour chiều cho ảnh đẹp nhất",
                'excerpt' => 'Khám phá rừng đỗ quyên cổ thụ tuyệt đẹp trên đỉnh Putaleng - khi thiên đường nở hoa.',
                'category' => 'stories',
                'tags' => ['putaleng', 'đỗ quyên', 'hoa', 'lai châu'],
                'status' => 'published',
                'is_featured' => false,
                'view_count' => 3890,
                'likes_count' => 312,
                'comments_count' => 56,
            ],

            // ---- Thêm vài draft ----
            [
                'title' => 'Top 10 đỉnh núi đẹp nhất Việt Nam phải chinh phục trước tuổi 30',
                'content' => "Danh sách 10 đỉnh núi mà dân yêu leo núi không thể bỏ qua...\n\n(Đang viết...)",
                'excerpt' => 'Danh sách 10 đỉnh núi đẹp nhất Việt Nam mà bạn nên chinh phục trước tuổi 30.',
                'category' => 'guide',
                'tags' => ['top 10', 'đỉnh núi', 'việt nam'],
                'status' => 'draft',
                'is_featured' => false,
                'view_count' => 0,
                'likes_count' => 0,
                'comments_count' => 0,
            ],
            [
                'title' => 'Review túi ngủ Aegismax cho leo núi mùa đông',
                'content' => "Túi ngủ lông vũ Aegismax M3 có xứng đáng với mức giá?\n\n(Đang viết...)",
                'excerpt' => 'Đánh giá túi ngủ lông vũ Aegismax M3 sau mùa đông leo núi.',
                'category' => 'reviews',
                'tags' => ['review', 'túi ngủ', 'trang bị'],
                'status' => 'draft',
                'is_featured' => false,
                'view_count' => 0,
                'likes_count' => 0,
                'comments_count' => 0,
            ],
        ];

        // Distribute posts among users
        $allUsers = array_merge(
            [User::where('email', 'user@gmail.com')->first()],
            $createdUsers
        );

        foreach ($blogPosts as $index => $postData) {
            $user = $allUsers[$index % count($allUsers)];
            if (!$user) continue;

            $slug = Str::slug($postData['title']);
            // Ensure unique slug
            $existingCount = BlogPost::where('slug', 'LIKE', $slug . '%')->count();
            if ($existingCount > 0) {
                $slug .= '-' . ($existingCount + 1);
            }

            $publishedAt = null;
            if ($postData['status'] === 'published') {
                $publishedAt = Carbon::now()->subDays(rand(1, 90))->subHours(rand(1, 23));
            }

            BlogPost::create([
                'user_id' => $user->id,
                'title' => $postData['title'],
                'slug' => $slug,
                'content' => $postData['content'],
                'excerpt' => $postData['excerpt'],
                'category' => $postData['category'],
                'tags' => $postData['tags'],
                'status' => $postData['status'],
                'is_featured' => $postData['is_featured'],
                'view_count' => $postData['view_count'],
                'likes_count' => $postData['likes_count'],
                'comments_count' => $postData['comments_count'],
                'published_at' => $publishedAt,
            ]);
        }

        $this->command->info('✅ Đã tạo ' . count($users) . ' users mới');
        $this->command->info('✅ Đã tạo ' . count($blogPosts) . ' blog posts');
    }
}
