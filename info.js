export default async function handler(req, res) {
  res.setHeader("Content-Type", "application/json");
  res.setHeader("Access-Control-Allow-Origin", "*");

  const username = req.query.username;
  if (!username) {
    return res.status(400).json({ error: "Thiếu username" });
  }

  const dinhDanh = username.startsWith("@")
    ? username.slice(1)
    : username;

  const url = `https://www.tiktok.com/@${dinhDanh}`;

  try {
    const response = await fetch(url, {
      headers: {
        "User-Agent":
          "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120 Safari/537.36",
      },
    });

    const html = await response.text();

    const match = (regex) => {
      const m = html.match(regex);
      return m ? m[1] : null;
    };

    const avatar = match(/"avatarLarger":"(.*?)"/);
    const profilePic = avatar ? avatar.replace(/\\u002F/g, "/") : null;

    const thong_tin = {
      user_id: match(/"user_id":"(\d+)"/),
      unique_id: match(/"uniqueId":"(.*?)"/),
      nickname: match(/"nickname":"(.*?)"/),
      followers: match(/"followerCount":(\d+)/),
      following: match(/"followingCount":(\d+)/),
      likes: match(/"heartCount":(\d+)/),
      videos: match(/"videoCount":(\d+)/),
      signature: match(/"signature":"(.*?)"/),
      verified: match(/"verified":(true|false)/),
      secUid: match(/"secUid":"(.*?)"/),
      privateAccount: match(/"privateAccount":(true|false)/),
      region: match(/"region":"(.*?)"/),
      heart: match(/"heart":(\d+)/),
      diggCount: match(/"diggCount":(\d+)/),
      friendCount: match(/"friendCount":(\d+)/),
      profile_pic: profilePic,
    };

    if (thong_tin.unique_id) {
      thong_tin.tiktok_link = `https://www.tiktok.com/@${thong_tin.unique_id}`;
    }

    return res.status(200).json(thong_tin);
  } catch (err) {
    return res.status(500).json({ error: "Không lấy được dữ liệu" });
  }
}