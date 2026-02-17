<?php

namespace Database\Seeders;

use App\Models\Journal;
use App\Models\User;
use App\Models\Tour;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class JournalSeeder extends Seeder
{
    /**
     * Seed dữ liệu mẫu cho nhật ký leo núi
     */
    public function run(): void
    {
        // Kiểm tra đã có journal chưa - tránh tạo trùng khi chạy lại
        if (Journal::count() > 0) {
            $this->command->info('⏭️  Đã có nhật ký trong database, bỏ qua JournalSeeder.');
            return;
        }

        // Lấy user mẫu (user@gmail.com = user_id 4, hoặc user đầu tiên có role 'user')
        $user = User::where('role', 'user')->first();
        if (!$user) {
            $this->command->warn('Không tìm thấy user nào có role "user". Bỏ qua JournalSeeder.');
            return;
        }

        // Lấy danh sách tour để liên kết
        $tours = Tour::where('is_active', true)->get();

        $journals = [
            [
                'user_id' => $user->id,
                'title' => 'Chinh phục đỉnh Fansipan - Nóc nhà Đông Dương',
                'content' => "Hôm nay là ngày mình chinh phục thành công đỉnh Fansipan! Xuất phát từ 5h sáng tại trạm Trạm Tôn, đoàn mình gồm 8 người bắt đầu hành trình leo bộ 11km. \n\nĐoạn đầu khá dễ đi, đường mòn rõ ràng qua rừng trúc. Nhưng từ km 5 trở đi bắt đầu khó, phải bám dây thừng leo đá. Nhiệt độ giảm dần, đến 2.800m thì sương mù dày đặc, không nhìn rõ đường.\n\nKhoảng 2h chiều thì lên tới đỉnh. Cảm xúc lúc đó thật khó tả - mệt rã rời nhưng hạnh phúc vô cùng! Biển mây bao phủ xung quanh, nắng chiếu qua tạo thành quầng sáng tuyệt đẹp.\n\nBài học rút ra: Luôn mang đủ nước (ít nhất 3 lít), áo gió chống nước, và tất len dày. Chân mình bị phồng rộp vì giày mới chưa đi quen 😅",
                'mood' => 'excited',
                'weather' => 'Sương mù, mát 12°C',
                'location' => 'Đỉnh Fansipan, Lào Cai',
                'latitude' => 22.3033,
                'longitude' => 103.7750,
                'altitude' => 3143,
                'images' => null,
                'privacy' => 'public',
                'tour_id' => $tours->where('name', 'like', '%Fansipan%')->first()?->id ?? $tours->first()?->id,
                'created_at' => Carbon::now()->subDays(15),
                'updated_at' => Carbon::now()->subDays(15),
            ],
            [
                'user_id' => $user->id,
                'title' => 'Săn mây Tà Xùa - Sống lưng khủng long',
                'content' => "3h sáng thức dậy trong cái lạnh 5°C, đoàn mình bắt đầu leo lên đỉnh Tà Xùa để kịp ngắm bình minh. Đường đi tối om, chỉ có ánh đèn pin soi từng bước.\n\nKhi mặt trời bắt đầu ló dạng, cảnh tượng thật sự gây sốc! Biển mây trắng xóa trải dài bất tận, những đỉnh núi nhô lên như hòn đảo giữa biển khơi. Dãy Sống Lưng Khủng Long hiện ra rõ nét duới ánh nắng vàng.\n\nĐây chắc chắn là một trong những cảnh đẹp nhất mình từng thấy. Ở đây khoảng 2 tiếng chụp ảnh và ngắm cảnh. Gió rất lớn nên phải mang áo ấm và kính mắt.\n\nTip: Nên đi vào mùa đông (tháng 11 - tháng 3) để có cơ hội cao nhất gặp biển mây. Nhớ check thời tiết trước khi đi!",
                'mood' => 'peaceful',
                'weather' => 'Trời quang, gió lớn 5°C',
                'location' => 'Tà Xùa, Sơn La',
                'latitude' => 21.2833,
                'longitude' => 104.5833,
                'altitude' => 2865,
                'images' => null,
                'privacy' => 'public',
                'tour_id' => $tours->where('name', 'like', '%Tà Xùa%')->first()?->id ?? $tours->skip(1)->first()?->id,
                'created_at' => Carbon::now()->subDays(12),
                'updated_at' => Carbon::now()->subDays(12),
            ],
            [
                'user_id' => $user->id,
                'title' => 'Hành trình gian nan Bạch Mộc Lương Tử',
                'content' => "2 ngày 1 đêm chinh phục Bạch Mộc Lương Tử - đỉnh núi cao thứ 4 Việt Nam. Đây thực sự là chuyến leo khó nhất mà mình từng trải qua.\n\nNgày 1: Xuất phát từ bản Sín Chải, đi qua những con suối, rừng tre rậm rạp. Đường rất trơn do mưa đêm trước. Đến 4h chiều thì tới điểm cắm trại ở độ cao 2.500m. Đêm đó nhiệt độ xuống 3°C, mặc 3 lớp áo mà vẫn rét run.\n\nNgày 2: 4h sáng xuất phát lên đỉnh. Đoạn cuối cùng phải vượt qua vách đá cheo leo, sử dụng dây thừng và kỹ năng leo vách. Tim đập thình thịch mỗi khi nhìn xuống.\n\nLên tới đỉnh lúc 8h sáng, trời trong vắt, nhìn 360° toàn bộ dãy Hoàng Liên Sơn. Cảm giác tự hào vô cùng! 💪",
                'mood' => 'challenged',
                'weather' => 'Mưa nhẹ, lạnh 3°C',
                'location' => 'Bạch Mộc Lương Tử, Lai Châu',
                'latitude' => 22.2167,
                'longitude' => 103.9500,
                'altitude' => 3046,
                'images' => null,
                'privacy' => 'public',
                'tour_id' => $tours->where('name', 'like', '%Bạch Mộc%')->first()?->id ?? $tours->skip(2)->first()?->id,
                'created_at' => Carbon::now()->subDays(8),
                'updated_at' => Carbon::now()->subDays(8),
            ],
            [
                'user_id' => $user->id,
                'title' => 'Leo núi Yên Tử - Hành trình tâm linh',
                'content' => "Chuyến đi nhẹ nhàng, thích hợp cho người mới bắt đầu leo núi. Yên Tử không quá cao nhưng con đường leo rất đẹp, hai bên là rừng trúc xanh mướt.\n\nXuất phát từ chân núi lúc 6h sáng, mình chọn đi bộ thay vì đi cáp treo để tận hưởng trọn vẹn hành trình. Đi qua chùa Hoa Yên, am Ngọa Vân, mỗi điểm dừng đều có nước uống và đồ ăn nhẹ.\n\nĐến chùa Đồng trên đỉnh lúc 10h, không khí mát lạnh, sương mù bao phủ tạo cảm giác thanh tịnh. Nhìn xuống thung lũng xanh ngát, thấy lòng mình bình yên.\n\nĐây là chuyến đi phù hợp cho gia đình hoặc người mới. Đường đi tốt, có bậc thang, và nhiều điểm nghỉ.",
                'mood' => 'peaceful',
                'weather' => 'Trời mát, sương nhẹ 18°C',
                'location' => 'Yên Tử, Quảng Ninh',
                'latitude' => 21.0618,
                'longitude' => 106.7175,
                'altitude' => 1068,
                'images' => null,
                'privacy' => 'public',
                'tour_id' => $tours->where('name', 'like', '%Yên Tử%')->first()?->id ?? $tours->skip(3)->first()?->id,
                'created_at' => Carbon::now()->subDays(5),
                'updated_at' => Carbon::now()->subDays(5),
            ],
            [
                'user_id' => $user->id,
                'title' => 'Trekking núi Chứa Chan - Gia Lào',
                'content' => "Cuối tuần rủ nhóm bạn 5 người đi leo núi Chứa Chan ở Đồng Nai. Đây là ngọn núi cao nhất vùng Đông Nam Bộ, rất thích hợp cho người ở TP.HCM muốn đi ngày.\n\nXuất phát từ chân núi lúc 5h30 sáng để tránh nắng. Đường lên khá dốc nhưng có bậc thang xi măng ở đoạn đầu. Từ giữa núi trở đi là đường mòn đất, phải bám rễ cây để leo.\n\nLên đỉnh mất khoảng 3 tiếng. View nhìn xuống hồ Trị An rất đẹp! Gió mát, nhiệt độ thấp hơn dưới chân khoảng 5-7°C.\n\nLưu ý: Mùa mưa đường rất trơn, nên đi vào mùa khô. Mang ít nhất 2 lít nước vì không có điểm bán nước trên đường.",
                'mood' => 'happy',
                'weather' => 'Nắng đẹp, 28°C',
                'location' => 'Núi Chứa Chan, Đồng Nai',
                'latitude' => 10.9500,
                'longitude' => 107.3667,
                'altitude' => 837,
                'images' => null,
                'privacy' => 'friends',
                'tour_id' => $tours->where('name', 'like', '%Chứa Chan%')->first()?->id ?? $tours->skip(4)->first()?->id,
                'created_at' => Carbon::now()->subDays(3),
                'updated_at' => Carbon::now()->subDays(3),
            ],
            [
                'user_id' => $user->id,
                'title' => 'Ngày nghỉ sau chuyến trek dài',
                'content' => "Hôm nay cơ thể mệt nhoài sau chuyến trek 2 ngày. Hai chân đau nhức, đặc biệt bắp chân và đầu gối. Nằm cả ngày ở homestay, uống trà gừng nóng và ăn phở.\n\nNhưng nhìn lại những bức ảnh chụp trên đỉnh, mình thấy xứng đáng với tất cả. Những khoảnh khắc đó không gì có thể mua được.\n\nGhi chú cho bản thân: Lần sau phải tập luyện thể lực trước ít nhất 2 tuần. Chạy bộ 5km/ngày và squat để làm quen với việc leo dốc.",
                'mood' => 'tired',
                'weather' => 'Âm u, mưa nhẹ 20°C',
                'location' => 'Homestay Sapa, Lào Cai',
                'latitude' => 22.3363,
                'longitude' => 103.8440,
                'altitude' => 1600,
                'images' => null,
                'privacy' => 'private',
                'tour_id' => null,
                'created_at' => Carbon::now()->subDays(2),
                'updated_at' => Carbon::now()->subDays(2),
            ],
            [
                'user_id' => $user->id,
                'title' => 'Lên kế hoạch chinh phục Lảo Thẩn',
                'content' => "Hôm nay ngồi nghiên cứu thông tin về đỉnh Lảo Thẩn ở Y Tý, Lào Cai. Đỉnh núi cao 2.860m, được mệnh danh là 'Thiên đường mây'. \n\nKế hoạch:\n- Thời điểm đi: Tháng 10-11 (mùa mây)\n- Đoàn: 6-8 người\n- Thời gian: 2 ngày 1 đêm\n- Cần chuẩn bị: Lều, túi ngủ -5°C, bếp gas mini, đồ ăn khô\n\nĐã liên hệ porter và guide bản địa, giá khoảng 500k/người/ngày. Cần đặt trước ít nhất 1 tuần.\n\nHứng khởi quá, đây sẽ là đỉnh thứ 5 trong danh sách chinh phục của mình! 🏔️",
                'mood' => 'excited',
                'weather' => 'Trời nắng, 25°C',
                'location' => 'TP Hồ Chí Minh',
                'latitude' => 10.7769,
                'longitude' => 106.7009,
                'altitude' => null,
                'images' => null,
                'privacy' => 'friends',
                'tour_id' => $tours->where('name', 'like', '%Lảo Thẩn%')->first()?->id ?? null,
                'created_at' => Carbon::now()->subDays(1),
                'updated_at' => Carbon::now()->subDays(1),
            ],
            [
                'user_id' => $user->id,
                'title' => 'Chuẩn bị đồ cho chuyến trekking mới',
                'content' => "Hôm nay đi mua sắm trang thiết bị cho chuyến trek sắp tới. Checklist đã chuẩn bị:\n\n✅ Giày trekking Salomon X Ultra 4 GTX - chống nước tốt\n✅ Ba lô 45L Osprey Atmos AG\n✅ Áo gió The North Face Venture 2\n✅ Tất len merino (3 đôi)\n✅ Găng tay chống lạnh\n✅ Đèn pin đội đầu Petzl\n✅ Bình nước giữ nhiệt 1L\n✅ Bộ sơ cứu y tế\n\nCòn thiếu:\n❌ Túi ngủ chịu được -5°C\n❌ Gậy trekking\n❌ Áo giữ nhiệt lớp base layer\n\nTổng chi phí trang bị: khoảng 8 triệu. Đắt nhưng đầu tư 1 lần dùng nhiều năm!",
                'mood' => 'happy',
                'weather' => 'Nắng ấm, 30°C',
                'location' => 'Quận 1, TP Hồ Chí Minh',
                'latitude' => 10.7731,
                'longitude' => 106.7030,
                'altitude' => null,
                'images' => null,
                'privacy' => 'public',
                'tour_id' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];

        foreach ($journals as $journalData) {
            Journal::create($journalData);
        }

        $this->command->info('✅ Đã tạo ' . count($journals) . ' nhật ký mẫu cho user: ' . $user->name . ' (' . $user->email . ')');
    }
}
