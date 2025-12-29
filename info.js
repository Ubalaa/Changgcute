export default async function handler(req, res) {
  // Thiết lập Header để hỗ trợ CORS và JSON
  res.setHeader("Content-Type", "application/json");
  res.setHeader("Access-Control-Allow-Origin", "*");

  const { username } = req.query;

  if (!username) {
    return res.status(400).json({ error: "Vui lòng nhập username" });
  }

  // Loại bỏ ký tự @ nếu người dùng nhập kèm
  const cleanUsername = username.startsWith("@") ? username.slice(1) : username;
  const url = `https://www.tiktok.com/@${cleanUsername}`;

  try {
    const response = await fetch(url, {
      headers: {
        "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36",
        "Accept-Language": "en-US,en;q=0.9",
      },
    });

    if (!response.ok) {
      throw new Error("Không thể truy cập TikTok");
    }

    const html = await response.text();

    // Tìm kiếm khối dữ liệu JSON chứa thông tin người dùng trong HTML
    const jsonRegex = /<script id="__UNIVERSAL_DATA_FOR_REHYDRATION__" type="application\/json">([\s\S]*?)<\/script>/;
    const match = html.match(jsonRegex);

    if (!match) {
      return res.status(404).json({ error: "Không tìm thấy dữ liệu người dùng. Có thể username sai hoặc bị chặn." });
    }

    const fullData = JSON.parse(match[1]);
    
    // Đường dẫn dữ liệu của TikTok thường nằm trong DefaultScope.webapp-user-detail
    const userData = fullData["__DEFAULT_SCOPE__"]?.["webapp.user-detail"]?.userInfo;

    if (!userData) {
      return res.status(404).json({ error: "Không tìm thấy thông tin chi tiết người dùng." });
    }

    const { user, stats } = userData;

    // Chuẩn hóa dữ liệu trả về
    const result = {
      user_id: user.id,
      unique_id: user.uniqueId,
      nickname: user.nickname,
      avatar: user.avatarLarger || user.avatarMedium || user.avatarThumb,
      signature: user.signature,
      verified: user.verified,
      region: user.region,
      privateAccount: user.privateAccount,
      secUid: user.secUid,
      followers: stats.followerCount,
      following: stats.followingCount,
      likes: stats.heartCount,
      videos: stats.videoCount,
      friendCount: stats.friendCount,
      tiktok_link: `https://www.tiktok.com/@${user.uniqueId}`
    };

    return res.status(200).json(result);

  } catch (error) {
    console.error("Error fetching TikTok data:", error);
    return res.status(500).json({ 
      error: "Lỗi máy chủ hoặc bị TikTok chặn truy cập (Rate Limit)",
      details: error.message 
    });
  }
}
