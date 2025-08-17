# [sso_discord](https://github.com/n138-kz/sso_discord)

## Activity

[![GitHub repo license](https://img.shields.io/github/license/n138-kz/sso_discord)](/LICENSE)
[![GitHub repo size](https://img.shields.io/github/repo-size/n138-kz/sso_discord)](/../../)
[![GitHub repo file count](https://img.shields.io/github/directory-file-count/n138-kz/sso_discord)](/../../)
[![GitHub code size in bytes](https://img.shields.io/github/languages/code-size/n138-kz/sso_discord)](/../../)
[![GitHub last commit](https://img.shields.io/github/last-commit/n138-kz/sso_discord)](/../../commits)
[![GitHub commit activity](https://img.shields.io/github/commit-activity/w/n138-kz/sso_discord)](/../../commits)
[![GitHub commit activity](https://img.shields.io/github/commit-activity/t/n138-kz/sso_discord)](/../../commits)
[![GitHub issues](https://img.shields.io/github/issues/n138-kz/sso_discord)](/../../issues)
[![GitHub issues closed](https://img.shields.io/github/issues-closed/n138-kz/sso_discord)](/../../issues)
[![GitHub pull requests closed](https://img.shields.io/github/issues-pr-closed/n138-kz/sso_discord)](/../../pulls)
[![GitHub pull requests](https://img.shields.io/github/issues-pr/n138-kz/sso_discord)](/../../pulls)
[![GitHub language count](https://img.shields.io/github/languages/count/n138-kz/sso_discord)](/../../)
[![GitHub top language](https://img.shields.io/github/languages/top/n138-kz/sso_discord)](/../../)

## Refs

- [![](https://www.google.com/s2/favicons?size=64&domain=https://discord.com)Discord公式リファレンス](https://discord.com/developers/docs/topics/oauth2)
- [![](https://www.google.com/s2/favicons?size=64&domain=https://discord.com)Developer Console](https://discord.com/developers/applications)
- [![](https://www.google.com/s2/favicons?size=64&domain=https://qiita.com)「DiscordのIDでログイン」を実装する(Oauth2)](https://qiita.com/masayoshi4649/items/46fdb744cb8255f5eb98)
- [![](https://www.google.com/s2/favicons?size=64&domain=https://qiita.com)PHP、CURLFileでファイルをアップロードする。(multipart/form-data、POST)](https://qiita.com/Pell/items/4ed98c906fd6a580a33f)
- [![](https://www.google.com/s2/favicons?size=64&domain=https://scrapbox.io)OAuth2 Scopesの一覧](https://scrapbox.io/discordwiki/OAuth2_Scopes%E3%81%AE%E4%B8%80%E8%A6%A7)
- [![](https://www.google.com/s2/favicons?size=64&domain=https://github.com)http_post](https://github.com/n138-kz/http_post)

<details>
<summary>PHP、CURLFileでファイルをアップロードする。(multipart/form-data、POST)</summary>

```php
$curl_file = new \CURLFile($_FILES['uploadfile']["tmp_name"], $_FILES['uploadfile']["type"], $_FILES['uploadfile']["name"]);
$option = [
    'aaa' => 'AAA',
    'bbb' => 'BBB',
];
$option_encode = json_encode($option);
$param = [
    // $curl_fileと、その他に必要なパラメータがあればここに追加。
    'file' => $curl_file,
    'pass' => "abcdef",
    'option' => $option_encode,
];
$postdata = $param;

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://xxx.jp/yyy/zzz.php");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: multipart/form-data"));
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
$result = curl_exec($ch);
$result_decode = json_decode($result, true);
```

</details>

## Flow

1. [OAuth2](https://discord.com/oauth2/authorize?client_id=1331215597119340585&response_type=code&redirect_uri=https%3A%2F%2Fn138-kz.github.io%2Fsso_discord%2F&scope=identify+email)を突いてもらい、Discord側の認証する
2. `redirect_url`先のページのURLに付いている`code`を送ってもらう
3. `code`を[Discord](https://discordapp.com/api/oauth2/token)に送り、`access_code`をもらう
4. `access_code`を使用してユーザの情報を拾う

> [!TIP]
> `code`はアクセスコードを貰うためだけのトークン  
> ユーザ情報とかは`access_code`を使用する

## config.json

Follow [config.json](/docs/config.json)

## Sample(shell)

```sh
client_id='1331215597119340585' # Follow the developer console, into bot info
client_secret='' # Follow the developer console, into bot info
redirect_uri='https://n138-kz.github.io/sso_discord/' # Follow the developer console, into bot info
access_code='' # Generate by discord api server after Click OAuth link then auth

access_token_raw=$(curl -X POST -H "Content-Type:application/x-www-form-urlencoded" -d "client_id=${client_id}&client_secret=${client_secret}&grant_type=authorization_code&code=${access_code}&redirect_uri=${redirect_uri}" https://discordapp.com/api/oauth2/token)
echo ${access_token_raw} | jq
access_token=$(echo ${access_token_raw} | jq -r .access_token)
curl -H "Authorization: Bearer ${access_token}" https://discordapp.com/api/users/@me
```

#### sample output

```json{
  "id": "80351110224678912",
  "username": "Nelly",
  "discriminator": "1337",
  "avatar": "8342729096ea3675442027381ff50dfe",
  "verified": true,
  "email": "nelly@discord.com",
  "flags": 64,
  "banner": "06c16474723fe537c283b8efa61a30c8",
  "accent_color": 16711680,
  "premium_type": 1,
  "public_flags": 64,
  "avatar_decoration_data": {
    "sku_id": "1144058844004233369",
    "asset": "a_fed43ab12698df65902ba06727e20c0e"
  },
  "collectibles": {
    "nameplate": {
      "sku_id": "2247558840304243311",
      "asset": "nameplates/nameplates/twilight/",
      "label": "",
      "palette": "cobalt"
    }
  },
  "primary_guild": {
    "identity_guild_id": "1234647491267808778",
    "identity_enabled": true,
    "tag": "DISC",
    "badge": "7d1734ae5a615e82bc7a4033b98fade8"
  }
}
```
https://discord.com/developers/docs/resources/user#user-object
https://discord.com/developers/docs/resources/user#get-current-user

##### User icon
```http
GET https://cdn.discordapp.com/avatars/Discord ID/アバターID
```
