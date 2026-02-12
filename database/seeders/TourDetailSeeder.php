<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tour;

class TourDetailSeeder extends Seeder
{
    public function run(): void
    {
        $tourDetails = [
            'Chinh phục đỉnh Fansipan' => [
                'overview' => 'Fansipan là đỉnh núi cao nhất Việt Nam và Đông Dương với độ cao 3.143m, được mệnh danh là "Nóc nhà Đông Dương". Hành trình chinh phục Fansipan là ước mơ của mọi trekker với những trải nghiệm độc đáo: xuyên qua rừng già nguyên sinh, chiêm ngưỡng hệ thực vật đa dạng, ngắm biển mây bồng bềnh và cảm nhận khí hậu 4 mùa trong một ngày. Đây là chuyến đi thử thách thể lực nhưng đem lại cảm giác thành tựu vô cùng to lớn.',
                'itinerary' => json_encode([
                    ['day' => 1, 'title' => 'Hà Nội - Sa Pa - Trạm Tôn', 'content' => 'Khởi hành từ Hà Nội, đến Sa Pa nhận phòng nghỉ ngơi. Chiều tham quan bản Cát Cát, thưởng thức ẩm thực địa phương.'],
                    ['day' => 2, 'title' => 'Trạm Tôn - Đỉnh Fansipan', 'content' => 'Xuất phát từ Trạm Tôn (1.900m), trekking qua rừng trúc, rừng đỗ quyên cổ thụ. Nghỉ đêm tại lán 2.800m hoặc chinh phục đỉnh trong ngày.'],
                    ['day' => 3, 'title' => 'Đỉnh Fansipan - Sa Pa - Hà Nội', 'content' => 'Ngắm bình minh trên đỉnh Fansipan (nếu nghỉ đêm), check-in cột mốc, xuống núi và về Hà Nội.']
                ]),
                'includes' => "- Xe đưa đón Hà Nội - Sa Pa - Hà Nội\n- 2 đêm khách sạn tại Sa Pa\n- Ăn sáng tại khách sạn\n- Porter mang đồ cá nhân (tối đa 5kg)\n- Hướng dẫn viên chuyên nghiệp\n- Lều trại, túi ngủ (nếu cắm trại)\n- Bảo hiểm du lịch\n- Phí vào cổng khu du lịch",
                'excludes' => "- Vé máy bay/tàu hỏa cá nhân\n- Đồ uống có cồn\n- Chi phí cá nhân phát sinh\n- Cáp treo Fansipan (nếu chọn đi cáp)\n- Tip cho porter và HDV",
                'highlights' => "- Chinh phục đỉnh núi cao nhất Đông Dương 3.143m\n- Ngắm biển mây hùng vĩ từ trên cao\n- Trải nghiệm khí hậu 4 mùa trong 1 ngày\n- Khám phá hệ sinh thái rừng nguyên sinh\n- Check-in cột mốc đỉnh Fansipan",
                'altitude' => 3143,
                'best_time' => 'Tháng 9 - Tháng 4',
                'map_lat' => 22.3033,
                'map_lng' => 103.7750,
                'gallery' => json_encode([
                    'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800',
                    'https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?w=800',
                    'https://images.unsplash.com/photo-1486870591958-9b9d0d1dda99?w=800',
                    'https://images.unsplash.com/photo-1519681393784-d120267933ba?w=800'
                ])
            ],
            'Trekking Tà Chì Nhù - Nóc nhà Tây Nguyên' => [
                'overview' => 'Tà Chì Nhù với độ cao 2.979m được mệnh danh là "Nóc nhà vùng Đông Bắc". Đây là một trong những cung trekking đẹp nhất miền Bắc với đồng cỏ xanh mướt, rừng thông bát ngát và view biển mây tuyệt đẹp. Hành trình phù hợp cho người có thể lực trung bình và muốn trải nghiệm cắm trại giữa thiên nhiên.',
                'itinerary' => json_encode([
                    ['day' => 1, 'title' => 'Hà Nội - Trạm Tấu - Chân núi', 'content' => 'Di chuyển từ Hà Nội đến Trạm Tấu (Yên Bái). Bắt đầu trekking từ bản Háng Phìn Nả, leo qua rừng thông và cắm trại tại đồng cỏ 2.400m.'],
                    ['day' => 2, 'title' => 'Đỉnh Tà Chì Nhù - Hà Nội', 'content' => 'Dậy sớm ngắm bình minh, chinh phục đỉnh Tà Chì Nhù. Chụp ảnh check-in và xuống núi, về Hà Nội.']
                ]),
                'includes' => "- Xe đưa đón Hà Nội - Trạm Tấu - Hà Nội\n- Lều trại 2 người/lều\n- Túi ngủ chống lạnh\n- Ăn tối và ăn sáng tại núi\n- Porter mang lều và đồ ăn\n- Hướng dẫn viên địa phương\n- Bảo hiểm du lịch",
                'excludes' => "- Đồ ăn vặt, nước uống cá nhân\n- Chi phí phát sinh\n- Gậy trekking (có thể thuê)\n- Tip cho đội ngũ hỗ trợ",
                'highlights' => "- Đồng cỏ xanh mướt trải dài\n- Rừng thông cổ thụ hùng vĩ\n- Săn mây bình minh tuyệt đẹp\n- Cắm trại giữa núi rừng\n- View 360 độ từ đỉnh núi",
                'altitude' => 2979,
                'best_time' => 'Tháng 10 - Tháng 5',
                'map_lat' => 21.4667,
                'map_lng' => 104.4500,
                'gallery' => json_encode([
                    'https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?w=800',
                    'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800',
                    'https://images.unsplash.com/photo-1454496522488-7a8e488e8606?w=800'
                ])
            ],
            'Khám phá Tà Xùa - Săn mây bình minh' => [
                'overview' => 'Tà Xùa là điểm săn mây nổi tiếng nhất Việt Nam với "sống lưng khủng long" độc đáo. Đỉnh núi cao 2.865m nằm giữa ranh giới Sơn La và Yên Bái, nơi có biển mây bồng bềnh quanh năm. Đây là thiên đường cho người yêu nhiếp ảnh và những ai muốn trải nghiệm cảm giác đứng trên mây.',
                'itinerary' => json_encode([
                    ['day' => 1, 'title' => 'Hà Nội - Bắc Yên - Bản Xà Phìn', 'content' => 'Khởi hành từ Hà Nội, đến Bắc Yên nhận phòng homestay. Chiều trekking nhẹ khám phá bản làng người Mông.'],
                    ['day' => 2, 'title' => 'Chinh phục đỉnh Tà Xùa', 'content' => 'Dậy sớm 3h sáng, trekking lên đỉnh Tà Xùa săn mây bình minh. Khám phá sống lưng khủng long và các đỉnh phụ.'],
                    ['day' => 3, 'title' => 'Tà Xùa - Hà Nội', 'content' => 'Ngắm mây một lần nữa (nếu thời tiết đẹp), ăn sáng và về Hà Nội.']
                ]),
                'includes' => "- Xe limousine Hà Nội - Bắc Yên - Hà Nội\n- 2 đêm homestay/khách sạn\n- Ăn sáng và ăn tối\n- Hướng dẫn viên địa phương\n- Đèn pin cho săn mây đêm\n- Bảo hiểm du lịch",
                'excludes' => "- Bữa trưa\n- Đồ uống cá nhân\n- Chi phí phát sinh\n- Quần áo ấm (tự chuẩn bị)\n- Tip cho HDV",
                'highlights' => "- Săn mây bình minh tuyệt đẹp\n- Đi trên sống lưng khủng long độc đáo\n- Biển mây bồng bềnh 360 độ\n- Trải nghiệm văn hóa người Mông\n- Ẩm thực vùng cao đặc sắc",
                'altitude' => 2865,
                'best_time' => 'Tháng 10 - Tháng 3',
                'map_lat' => 21.2833,
                'map_lng' => 104.2167,
                'gallery' => json_encode([
                    'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800',
                    'https://images.unsplash.com/photo-1519681393784-d120267933ba?w=800',
                    'https://images.unsplash.com/photo-1486870591958-9b9d0d1dda99?w=800'
                ])
            ],
            'Pù Luông - Thiên đường xanh' => [
                'overview' => 'Pù Luông là khu bảo tồn thiên nhiên nằm ở Thanh Hóa với những ruộng bậc thang đẹp như tranh vẽ, suối nước trong vắt và không khí trong lành. Đây là điểm đến lý tưởng cho kỳ nghỉ cuối tuần với gia đình, phù hợp mọi lứa tuổi với các hoạt động trekking nhẹ nhàng, tắm suối và trải nghiệm homestay.',
                'itinerary' => json_encode([
                    ['day' => 1, 'title' => 'Hà Nội - Pù Luông', 'content' => 'Khởi hành từ Hà Nội, đến Pù Luông check-in homestay. Chiều trekking ngắm ruộng bậc thang, tắm suối và thưởng thức BBQ tối.'],
                    ['day' => 2, 'title' => 'Pù Luông - Hà Nội', 'content' => 'Sáng dậy sớm ngắm bình minh trên ruộng bậc thang. Ăn sáng, tham quan thác Hiêu và về Hà Nội.']
                ]),
                'includes' => "- Xe đưa đón Hà Nội - Pù Luông - Hà Nội\n- 1 đêm homestay bungalow\n- Ăn tối BBQ và ăn sáng\n- Hướng dẫn viên địa phương\n- Vé tham quan thác Hiêu\n- Bảo hiểm du lịch",
                'excludes' => "- Bữa trưa\n- Đồ uống có cồn\n- Massage, spa\n- Chi phí cá nhân\n- Tip cho homestay",
                'highlights' => "- Ruộng bậc thang tuyệt đẹp\n- Không khí trong lành mát mẻ\n- Tắm suối thiên nhiên\n- Homestay view đẹp\n- Ẩm thực Mường đặc sắc",
                'altitude' => 1700,
                'best_time' => 'Tháng 5 - Tháng 10',
                'map_lat' => 20.4500,
                'map_lng' => 105.1667,
                'gallery' => json_encode([
                    'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800',
                    'https://images.unsplash.com/photo-1473773508845-188df298d2d1?w=800',
                    'https://images.unsplash.com/photo-1501785888041-af3ef285b470?w=800'
                ])
            ],
            'Bạch Mộc Lương Tử - Ngao du xứ tuyết' => [
                'overview' => 'Bạch Mộc Lương Tử cao 3.046m là một trong "Tứ đại đỉnh" của Việt Nam, nổi tiếng với cảnh đẹp hoang sơ và khả năng có tuyết rơi vào mùa đông. Đây là cung trekking thử thách dành cho những trekker có kinh nghiệm, với địa hình hiểm trở nhưng đem lại trải nghiệm vô cùng đáng giá.',
                'itinerary' => json_encode([
                    ['day' => 1, 'title' => 'Hà Nội - Sa Pa - Sín Chải', 'content' => 'Di chuyển từ Hà Nội đến Sa Pa, tiếp tục đến bản Sín Chải. Nghỉ đêm tại nhà dân.'],
                    ['day' => 2, 'title' => 'Sín Chải - Lán 2.400m', 'content' => 'Bắt đầu trekking từ Sín Chải, xuyên qua rừng già và suối. Cắm trại tại độ cao 2.400m.'],
                    ['day' => 3, 'title' => 'Lán - Đỉnh BMLT - Lán', 'content' => 'Dậy sớm chinh phục đỉnh, ngắm bình minh và quay về lán nghỉ.'],
                    ['day' => 4, 'title' => 'Lán - Sín Chải - Hà Nội', 'content' => 'Xuống núi về bản Sín Chải, di chuyển về Hà Nội.']
                ]),
                'includes' => "- Xe đưa đón Hà Nội - Sín Chải - Hà Nội\n- 3 đêm (1 đêm nhà dân + 2 đêm lều)\n- Ăn uống đầy đủ trong tour\n- Lều, túi ngủ chống lạnh -10 độ\n- Porter chuyên nghiệp\n- Hướng dẫn viên bản địa\n- Bảo hiểm du lịch cao cấp",
                'excludes' => "- Đồ uống có cồn\n- Quần áo chống lạnh (tự chuẩn bị)\n- Gậy trekking\n- Chi phí phát sinh\n- Tip cho đội ngũ",
                'highlights' => "- Chinh phục Tứ đại đỉnh Việt Nam\n- Cơ hội ngắm tuyết rơi (mùa đông)\n- Địa hình hoang sơ thử thách\n- Cắm trại giữa rừng nguyên sinh\n- Trải nghiệm văn hóa người Dao đỏ",
                'altitude' => 3046,
                'best_time' => 'Tháng 10 - Tháng 3',
                'map_lat' => 22.3667,
                'map_lng' => 103.7333,
                'gallery' => json_encode([
                    'https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?w=800',
                    'https://images.unsplash.com/photo-1519681393784-d120267933ba?w=800',
                    'https://images.unsplash.com/photo-1486870591958-9b9d0d1dda99?w=800',
                    'https://images.unsplash.com/photo-1454496522488-7a8e488e8606?w=800'
                ])
            ],
            'Ngũ Chỉ Sơn - Kỳ quan Đông Bắc' => [
                'overview' => 'Ngũ Chỉ Sơn là dãy núi độc đáo với 5 đỉnh nhọn như 5 ngón tay khổng lồ vươn lên trời. Nằm ở Lạng Sơn, nơi đây có hệ thống hang động kỳ vĩ, thác nước đẹp và cảnh quan núi non hùng vĩ. Tour kết hợp trekking nhẹ và tham quan văn hóa Tày - Nùng.',
                'itinerary' => json_encode([
                    ['day' => 1, 'title' => 'Hà Nội - Bắc Sơn', 'content' => 'Khởi hành từ Hà Nội, đến thung lũng Bắc Sơn ngắm ruộng bậc thang. Chiều tham quan đình Nông Lục, nghỉ đêm tại homestay.'],
                    ['day' => 2, 'title' => 'Bắc Sơn - Ngũ Chỉ Sơn - Hà Nội', 'content' => 'Sáng trekking lên điểm ngắm Ngũ Chỉ Sơn, tham quan hang động và về Hà Nội.']
                ]),
                'includes' => "- Xe đưa đón Hà Nội - Bắc Sơn - Hà Nội\n- 1 đêm homestay\n- Ăn tối và ăn sáng\n- Hướng dẫn viên địa phương\n- Vé tham quan các điểm\n- Bảo hiểm du lịch",
                'excludes' => "- Bữa trưa\n- Đồ uống cá nhân\n- Chi phí cá nhân\n- Tip cho HDV",
                'highlights' => "- Ngắm dãy Ngũ Chỉ Sơn hùng vĩ\n- Thung lũng Bắc Sơn tuyệt đẹp\n- Khám phá hang động kỳ bí\n- Văn hóa Tày - Nùng đặc sắc\n- Ẩm thực vùng cao ngon lành",
                'altitude' => 1500,
                'best_time' => 'Tháng 9 - Tháng 11',
                'map_lat' => 21.8333,
                'map_lng' => 106.3167,
                'gallery' => json_encode([
                    'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800',
                    'https://images.unsplash.com/photo-1473773508845-188df298d2d1?w=800',
                    'https://images.unsplash.com/photo-1501785888041-af3ef285b470?w=800'
                ])
            ],
            'Núi Chứa Chan - Thiên đường miền Nam' => [
                'overview' => 'Núi Chứa Chan (Gia Lào) cao 837m là điểm leo núi phổ biến nhất khu vực phía Nam, chỉ cách TP.HCM khoảng 100km. Đây là điểm đến lý tưởng cho người mới bắt đầu leo núi với đường mòn rõ ràng, có chùa và tượng Phật lớn trên đỉnh. Tour phù hợp đi về trong ngày.',
                'itinerary' => json_encode([
                    ['day' => 1, 'title' => 'TP.HCM - Núi Chứa Chan - TP.HCM', 'content' => 'Khởi hành sớm từ TP.HCM, đến chân núi và bắt đầu leo. Thời gian leo khoảng 2-3 tiếng. Nghỉ ngơi, tham quan chùa trên đỉnh và xuống núi về TP.HCM.']
                ]),
                'includes' => "- Xe đưa đón TP.HCM - Đồng Nai - TP.HCM\n- Nước uống\n- Hướng dẫn viên\n- Bảo hiểm du lịch",
                'excludes' => "- Ăn sáng, ăn trưa\n- Chi phí cá nhân\n- Lễ vật cúng chùa\n- Tip cho HDV",
                'highlights' => "- Phù hợp người mới bắt đầu\n- Đường mòn rõ ràng an toàn\n- Tượng Phật lớn trên đỉnh\n- View toàn cảnh Đồng Nai\n- Không khí trong lành",
                'altitude' => 837,
                'best_time' => 'Quanh năm',
                'map_lat' => 10.9500,
                'map_lng' => 107.2000,
                'gallery' => json_encode([
                    'https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?w=800',
                    'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800'
                ])
            ],
            'Lảo Thẩn - Săn mây Y Tý' => [
                'overview' => 'Lảo Thẩn cao 2.865m nằm tại Y Tý - Lào Cai, là một trong những điểm săn mây đẹp nhất Việt Nam. Nơi đây có ruộng bậc thang nổi tiếng, biển mây hùng vĩ và văn hóa dân tộc Hà Nhì độc đáo. Tour phù hợp cho người yêu nhiếp ảnh và muốn khám phá vùng đất hoang sơ.',
                'itinerary' => json_encode([
                    ['day' => 1, 'title' => 'Hà Nội - Lào Cai - Y Tý', 'content' => 'Di chuyển từ Hà Nội đến Y Tý, check-in homestay. Chiều khám phá bản làng Hà Nhì, ngắm hoàng hôn trên ruộng bậc thang.'],
                    ['day' => 2, 'title' => 'Săn mây Lảo Thẩn', 'content' => 'Dậy sớm 4h sáng, di chuyển lên đỉnh Lảo Thẩn săn mây bình minh. Chiều tham quan ruộng bậc thang Y Tý.'],
                    ['day' => 3, 'title' => 'Y Tý - Hà Nội', 'content' => 'Săn mây một lần nữa (tùy thời tiết), tham quan chợ phiên (nếu có) và về Hà Nội.']
                ]),
                'includes' => "- Xe đưa đón Hà Nội - Y Tý - Hà Nội\n- 2 đêm homestay\n- Ăn sáng và ăn tối\n- Hướng dẫn viên địa phương\n- Xe bán tải lên đỉnh săn mây\n- Bảo hiểm du lịch",
                'excludes' => "- Bữa trưa\n- Đồ uống cá nhân\n- Quần áo ấm (tự chuẩn bị)\n- Tip cho HDV và lái xe",
                'highlights' => "- Săn mây bình minh tuyệt đẹp\n- Ruộng bậc thang Y Tý nổi tiếng\n- Văn hóa dân tộc Hà Nhì độc đáo\n- Ẩm thực vùng cao đặc sắc\n- Cảnh quan hoang sơ hùng vĩ",
                'altitude' => 2865,
                'best_time' => 'Tháng 9 - Tháng 4',
                'map_lat' => 22.6167,
                'map_lng' => 103.6167,
                'gallery' => json_encode([
                    'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800',
                    'https://images.unsplash.com/photo-1519681393784-d120267933ba?w=800',
                    'https://images.unsplash.com/photo-1486870591958-9b9d0d1dda99?w=800',
                    'https://images.unsplash.com/photo-1473773508845-188df298d2d1?w=800'
                ])
            ]
        ];

        foreach ($tourDetails as $name => $details) {
            $tour = Tour::where('name', $name)->first();
            if ($tour) {
                // Decode JSON nếu là string
                if (isset($details['itinerary']) && is_string($details['itinerary'])) {
                    $details['itinerary'] = json_decode($details['itinerary'], true);
                }
                if (isset($details['gallery']) && is_string($details['gallery'])) {
                    $details['gallery'] = json_decode($details['gallery'], true);
                }
                
                $tour->update($details);
                $this->command->info("Đã cập nhật tour: {$name}");
            } else {
                $this->command->warn("Không tìm thấy tour: {$name}");
            }
        }

        $this->command->info('Hoàn thành cập nhật chi tiết tour!');
    }
}
