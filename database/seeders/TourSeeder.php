<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tour;
use Illuminate\Support\Str;

class TourSeeder extends Seeder
{
    public function run(): void
    {
        $tours = [
            [
                'name' => 'Chinh phục đỉnh Fansipan',
                'description' => 'Hành trình chinh phục "Nóc nhà Đông Dương" - đỉnh Fansipan cao 3.143m tại Sa Pa, Lào Cai. Trải nghiệm khí hậu 4 mùa trong một ngày, ngắm nhìn biển mây hùng vĩ và cảnh sắc núi rừng Tây Bắc.',
                'location' => 'Sa Pa, Lào Cai',
                'difficulty' => 'hard',
                'duration_days' => 3,
                'image' => 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800',
                'is_active' => true,
                'overview' => 'Fansipan là đỉnh núi cao nhất Việt Nam và Đông Dương với độ cao 3.143m so với mặt nước biển. Được mệnh danh là "Nóc nhà Đông Dương", Fansipan là thử thách đầy hấp dẫn đối với những người yêu thích leo núi và khám phá thiên nhiên.',
                'itinerary' => json_encode([
                    'Ngày 1: Sa Pa - Trạm Tôn - Cắm trại (4-5h trekking)',
                    'Ngày 2: Trạm Tôn - Đỉnh Fansipan - Xuống núi (6-7h)',
                    'Ngày 3: Về Sa Pa - Kết thúc tour'
                ]),
                'includes' => "• Xe đưa đón từ Sa Pa\n• Hướng dẫn viên chuyên nghiệp\n• Phụ trách porter\n• Bảo hiểm du lịch\n• Lều cắm trại và sleeping bag\n• 2 bữa sáng, 3 bữa trưa, 2 bữa tối\n• Nước suối và snack",
                'excludes' => "• Chi phí cá nhân\n• Đồ uống có cồn\n• Tips cho hướng dẫn viên\n• Vé cáp treo (nếu chọn xuống bằng cáp treo)",
                'highlights' => "• Chinh phục đỉnh núi cao nhất Đông Dương\n• Trekking qua rừng già nguyên sinh\n• Ngắm biển mây hùng vĩ\n• Cắm trại trên núi cao\n• Trải nghiệm khí hậu 4 mùa trong 1 ngày",
                'altitude' => 3143,
                'best_time' => 'Tháng 10-12, Tháng 3-5',
                'map_lat' => 22.302498,
                'map_lng' => 103.775454,
            ],
            [
                'name' => 'Trekking Tà Chì Nhù - Nóc nhà Tây Nguyên',
                'description' => 'Chinh phục đỉnh Tà Chì Nhù cao 2.979m - "Nóc nhà Tây Nguyên" tại Gia Lai. Hành trình xuyên qua rừng thông bát ngát, đồng cỏ xanh mướt và những thung lũng hoang sơ tuyệt đẹp.',
                'location' => 'Mang Yang, Gia Lai',
                'difficulty' => 'medium',
                'duration_days' => 3,
                'image' => 'https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?w=800',
                'is_active' => true,
                'overview' => 'Tà Chì Nhù là đỉnh núi cao nhất Tây Nguyên với độ cao 2.979m, nằm giữa ranh giới Gia Lai và Kon Tum. Nơi đây sở hữu phong cảnh núi non hùng vĩ, rừng thông bao la và khí hậu mát mẻ quanh năm.',
                'itinerary' => json_encode([
                    'Ngày 1: Pleiku - Mang Yang - Trại căn cứ (5h trekking)',
                    'Ngày 2: Trại căn cứ - Đỉnh Tà Chì Nhù - Xuống núi (7h)',
                    'Ngày 3: Về Pleiku - Kết thúc tour'
                ]),
                'includes' => "• Xe đưa đón từ Pleiku\n• Hướng dẫn viên địa phương\n• Porter hỗ trợ hành lý\n• Bảo hiểm\n• Cắm trại và sleeping bag\n• 2 bữa sáng, 3 bữa trưa, 2 bữa tối",
                'excludes' => "• Vé máy bay đến Pleiku\n• Chi phí cá nhân\n• Đồ uống có cồn",
                'highlights' => "• Chinh phục nóc nhà Tây Nguyên\n• Rừng thông bạt ngàn\n• Đồng cỏ xanh mướt\n• Cắm trại trên núi cao\n• Bình minh và hoàng hôn tuyệt đẹp",
                'altitude' => 2979,
                'best_time' => 'Tháng 11-4 (mùa khô)',
                'map_lat' => 14.283333,
                'map_lng' => 108.500000,
            ],
            [
                'name' => 'Khám phá Tà Xùa - Săn mây bình minh',
                'description' => 'Chinh phục dãy Tà Xùa - "Thiên đường săn mây" của miền Bắc. Trải nghiệm cắm trại trên lưng khủng long, ngắm biển mây bồng bềnh và bình minh tuyệt đẹp trên đỉnh núi.',
                'location' => 'Bắc Yên, Sơn La',
                'difficulty' => 'hard',
                'duration_days' => 3,
                'image' => 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800',
                'is_active' => true,
                'overview' => 'Tà Xùa là một trong những địa điểm săn mây đẹp nhất Việt Nam với dãy núi uốn lượn như lưng khủng long. Độ cao 2.865m, khí hậu mát mẻ quanh năm và biển mây bồng bềnh tạo nên một khung cảnh huyền ảo.',
                'itinerary' => json_encode([
                    'Ngày 1: Hà Nội - Bắc Yên - Trại cắm trại Tà Xùa',
                    'Ngày 2: Săn mây bình minh - Chinh phục đỉnh - Xuống núi',
                    'Ngày 3: Về Hà Nội'
                ]),
                'includes' => "• Xe limousine Hà Nội - Tà Xùa - Hà Nội\n• Hướng dẫn viên nhiều kinh nghiệm\n• Lều và sleeping bag\n• Bảo hiểm toàn tour\n• 2 bữa sáng, 2 bữa trưa, 2 bữa tối",
                'excludes' => "• Chi phí cá nhân\n• Đồ uống ngoài bữa ăn\n• Tips",
                'highlights' => "• Cắm trại trên lưng khủng long\n• Săn mây bình minh tuyệt đẹp\n• Biển mây bồng bềnh\n• Cảnh núi non hùng vĩ\n• Thử thách leo dốc thú vị",
                'altitude' => 2865,
                'best_time' => 'Tháng 10-3 (mùa săn mây)',
                'map_lat' => 21.283333,
                'map_lng' => 104.150000,
            ],
            [
                'name' => 'Pù Luông - Thiên đường xanh',
                'description' => 'Khám phá khu bảo tồn thiên nhiên Pù Luông với những ruộng bậc thang tuyệt đẹp, bản làng yên bình và thiên nhiên hoang sơ. Hành trình trekking nhẹ nhàng phù hợp mọi lứa tuổi.',
                'location' => 'Pù Luông, Thanh Hóa',
                'difficulty' => 'easy',
                'duration_days' => 3,
                'image' => 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800',
                'is_active' => true,
                'overview' => 'Pù Luông là khu bảo tồn thiên nhiên tuyệt đẹp với ruộng bậc thang xanh mướt, suối nước trong vắt và những bản làng thái cổ kính. Đây là điểm đến lý tưởng cho những ai muốn nghỉ ngơi, thư giãn và khám phá văn hóa bản địa.',
                'itinerary' => json_encode([
                    'Ngày 1: Hà Nội - Pù Luông - Homestay',
                    'Ngày 2: Trekking thác, ruộng bậc thang - Bản làng',
                    'Ngày 3: Về Hà Nội'
                ]),
                'includes' => "• Xe đưa đón từ Hà Nội\n• Homestay bản địa\n• Hướng dẫn viên\n• 2 bữa sáng, 3 bữa trưa, 2 bữa tối\n• Bảo hiểm",
                'excludes' => "• Chi phí cá nhân\n• Đồ uống có cồn",
                'highlights' => "• Ruộng bậc thang tuyệt đẹp\n• Homestay văn hóa Thái\n• Suối nước trong vắt\n• Bản làng yên bình\n• Ẩm thực địa phương",
                'altitude' => 1700,
                'best_time' => 'Tháng 4-5, 9-10 (lúa chín)',
                'map_lat' => 20.533333,
                'map_lng' => 105.233333,
            ],
            [
                'name' => 'Bạch Mộc Lương Tử - Ngao du xứ tuyết',
                'description' => 'Chinh phục đỉnh Bạch Mộc Lương Tử cao 3.046m - "Tứ đại đỉnh" của Việt Nam. Trải nghiệm khí hậu lạnh giá, ngắm băng tuyết mùa đông và cảnh sắc núi non hùng vĩ.',
                'location' => 'Sa Pa, Lào Cai',
                'difficulty' => 'hard',
                'duration_days' => 3,
                'image' => 'https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?w=800',
                'is_active' => true,
                'overview' => 'Bạch Mộc Lương Tử là đỉnh núi cao thứ 4 Việt Nam với độ cao 3.046m. Nổi tiếng với băng tuyết phủ trắng vào mùa đông và cảnh quan núi non hùng vĩ. Tour dành cho người có kinh nghiệm và thể lực tốt.',
                'itinerary' => json_encode([
                    'Ngày 1: Sa Pa - Trại San Sả Hồ (6h trekking)',
                    'Ngày 2: Trại - Đỉnh Bạch Mộc - Xuống trại (8h)',
                    'Ngày 3: Về Sa Pa'
                ]),
                'includes' => "• Xe đưa đón Sa Pa\n• Hướng dẫn viên chuyên nghiệp\n• Porter hỗ trợ\n• Lều cắm trại và sleeping bag -10°C\n• 2 bữa sáng, 3 bữa trưa, 2 bữa tối\n• Bảo hiểm",
                'excludes' => "• Vé máy bay/tàu đến Lào Cai\n• Trang phục leo núi chuyên dụng\n• Chi phí cá nhân",
                'highlights' => "• Chinh phục Tứ đại đỉnh\n• Ngắm băng tuyết mùa đông\n• Cảnh núi non hùng vĩ\n• Thử thách khắc nghiệt\n• Trải nghiệm cực hạn",
                'altitude' => 3046,
                'best_time' => 'Tháng 11-2 (có tuyết)',
                'map_lat' => 22.416667,
                'map_lng' => 103.683333,
            ],
            [
                'name' => 'Ngũ Chỉ Sơn - Kỳ quan Đông Bắc',
                'description' => 'Khám phá dãy núi Ngũ Chỉ Sơn với hệ thống hang động kỳ vĩ, thác nước hùng vĩ và cảnh quan non nước đẹp như tranh vẽ tại Lạng Sơn.',
                'location' => 'Bắc Sơn, Lạng Sơn',
                'difficulty' => 'medium',
                'duration_days' => 3,
                'image' => 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800',
                'is_active' => true,
                'overview' => 'Ngũ Chỉ Sơn là dãy núi đá vôi hùng vĩ với 5 ngọn núi sừng sững như 5 ngón tay khổng lồ. Nơi đây có nhiều hang động đẹp, thác nước và di tích lịch sử cách mạng.',
                'itinerary' => json_encode([
                    'Ngày 1: Hà Nội - Lạng Sơn - Tham quan hang động',
                    'Ngày 2: Trekking Ngũ Chỉ Sơn - Thác Bản Giốc',
                    'Ngày 3: Về Hà Nội'
                ]),
                'includes' => "• Xe đưa đón từ Hà Nội\n• Khách sạn 3 sao\n• Hướng dẫn viên\n• Vé tham quan\n• 2 bữa sáng, 3 bữa trưa, 2 bữa tối",
                'excludes' => "• Chi phí cá nhân\n• Đồ uống có cồn",
                'highlights' => "• Dãy núi Ngũ Chỉ Sơn kỳ vĩ\n• Hang động đẹp\n• Thác Bản Giốc\n• Văn hóa Tày-Nùng\n• Di tích lịch sử",
                'altitude' => 1541,
                'best_time' => 'Tháng 9-11',
                'map_lat' => 21.916667,
                'map_lng' => 106.283333,
            ],
            [
                'name' => 'Núi Chứa Chan - Thiên đường miền Nam',
                'description' => 'Chinh phục núi Chứa Chan cao 837m tại Đồng Nai - điểm leo núi gần Sài Gòn. Thích hợp cho người mới bắt đầu với đường mòn rõ ràng và view đẹp.',
                'location' => 'Đồng Nai',
                'difficulty' => 'easy',
                'duration_days' => 1,
                'image' => 'https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?w=800',
                'is_active' => true,
                'overview' => 'Núi Chứa Chan là điểm leo núi lý tưởng cho người Sài Gòn với độ cao 837m. Đường mòn rõ ràng, có thể đi về trong ngày. Đỉnh núi có tượng Phật lớn và tầm nhìn tuyệt đẹp.',
                'itinerary' => json_encode([
                    'Sáng: TP.HCM - Đồng Nai - Leo núi Chứa Chan',
                    'Trưa: Nghỉ ngơi và ăn trưa trên đỉnh',
                    'Chiều: Xuống núi - Về TP.HCM'
                ]),
                'includes' => "• Xe đưa đón từ TP.HCM\n• Hướng dẫn viên\n• 1 bữa trưa\n• Nước suối\n• Bảo hiểm",
                'excludes' => "• Chi phí cá nhân\n• Đồ uống ngoài bữa ăn",
                'highlights' => "• Leo núi gần Sài Gòn\n• Phù hợp người mới\n• Tượng Phật đỉnh núi\n• View tuyệt đẹp\n• Đi về trong ngày",
                'altitude' => 837,
                'best_time' => 'Quanh năm',
                'map_lat' => 11.000000,
                'map_lng' => 107.083333,
            ],
            [
                'name' => 'Lảo Thẩn - Săn mây Y Tý',
                'description' => 'Khám phá đỉnh Lảo Thẩn cao 2.865m tại Y Tý - Lào Cai. Nơi có ruộng bậc thang đẹp nhất Việt Nam, biển mây hùng vĩ và văn hóa dân tộc Hà Nhì độc đáo.',
                'location' => 'Y Tý, Lào Cai',
                'difficulty' => 'medium',
                'duration_days' => 3,
                'image' => 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800',
                'is_active' => true,
                'overview' => 'Y Tý là vùng đất cao nguyên tuyệt đẹp với ruộng bậc thang như tranh vẽ, biển mây bồng bềnh và văn hóa Hà Nhì đặc sắc. Lảo Thẩn cao 2.865m là điểm ngắm cảnh tuyệt vời.',
                'itinerary' => json_encode([
                    'Ngày 1: Lào Cai - Y Tý - Homestay',
                    'Ngày 2: Trekking Lảo Thẩn - Săn mây - Ruộng bậc thang',
                    'Ngày 3: Về Lào Cai'
                ]),
                'includes' => "• Xe đưa đón từ Lào Cai\n• Homestay bản địa\n• Hướng dẫn viên\n• 2 bữa sáng, 3 bữa trưa, 2 bữa tối\n• Bảo hiểm",
                'excludes' => "• Vé tàu/máy bay đến Lào Cai\n• Chi phí cá nhân",
                'highlights' => "• Ruộng bậc thang đẹp nhất VN\n• Săn mây tuyệt đẹp\n• Văn hóa Hà Nhì\n• Homestay độc đáo\n• Nhiếp ảnh tuyệt vời",
                'altitude' => 2865,
                'best_time' => 'Tháng 9-11, 5-6',
                'map_lat' => 22.633333,
                'map_lng' => 103.683333,
            ],
        ];

        foreach ($tours as $tourData) {
            Tour::updateOrCreate(
                [
                    'name' => $tourData['name'],
                    'location' => $tourData['location']
                ],
                $tourData
            );
        }
    }
}
