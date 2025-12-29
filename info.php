const express = require('express');
const axios = require('axios');
const app = express();

// Thiết lập header giống PHP
app.use((req, res, next) => {
    res.header("Content-Type", "application/json");
    res.header("Access-Control-Allow-Origin", "*");
    next();
});

async function lay_thong_tin_nguoi_dung(dinh_danh) {
    // Logic xử lý dấu @ giống hệt PHP
    if (dinh_danh.startsWith('@')) {
        dinh_danh = dinh_danh.substring(1);
    }
    const url = `https://www.tiktok.com/@${dinh_danh}`;

    try {
        const response = await axios.get(url, {
            headers: {
                'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
            }
        });

        const html = response.data;

        // Hàm helper để giả lập preg_match_all của PHP
        const match = (regex) => {
            const m = html.match(regex);
            return m ? m[1] : null;
        };

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
            // Xử lý replace ký tự unicode giống str_replace trong PHP
            profile_pic: match(/"avatarLarger":"(.*?)"/)?.replace(/\\u002F/g, '/'),
            tiktok_link: null
        };

        // Gán link tiktok nếu tìm thấy unique_id
        if (thong_tin.unique_id) {
            thong_tin.tiktok_link = `https://www.tiktok.com/@${thong_tin.unique_id}`;
        }

        return thong_tin;

    } catch (error) {
        return null;
    }
}

// Route xử lý: ?username=...
app.get('/', async (req, res) => {
    const username = req.query.username;
    if (username) {
        const data = await lay_thong_tin_nguoi_dung(username);
        res.send(JSON.stringify(data));
    } else {
        res.send(JSON.stringify(null));
    }
});

const PORT = process.env.PORT || 3000;
app.listen(PORT, () => {
    console.log(`Server chạy tại cổng ${PORT}`);
});
