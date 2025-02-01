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

- [Discord公式リファレンス](https://discord.com/developers/docs/topics/oauth2)
- [Developer Console](https://discord.com/developers/applications)
- [「DiscordのIDでログイン」を実装する(Oauth2)](https://qiita.com/masayoshi4649/items/46fdb744cb8255f5eb98)

## Flow

1. [OAuth2](https://discord.com/oauth2/authorize?client_id=1331215597119340585&response_type=code&redirect_uri=https%3A%2F%2Fn138-kz.github.io%2Fsso_discord%2F&scope=identify+email)を突いてもらい、Discord側の認証する
2. `redirect_url`先のページのURLに付いている`code`を送ってもらう
3. `code`を[Discord](#)に送り、`access_code`をもらう
4. `access_code`を使用してユーザの情報を拾う

> [!TIP]
> `code`はアクセスコードを貰うためだけのトークン  
> ユーザ情報とかは`access_code`を使用する

## Setup(memo)

- [client](#client)
- [server](#server)

### client

1. Download the this repository. Or view https://n138-kz.github.io/sso_discord

<a href="https://discord.com/oauth2/authorize?client_id=1331215597119340585&response_type=code&redirect_uri=https%3A%2F%2Fn138-kz.github.io%2Fsso_discord%2F&scope=identify+email" target="_self" style="border-radius: 1em;background-color: #5763f2;color:#fff;background-image:url('https://n138-kz.github.io/sso_discord/discord-icon.webp');background-repeat:no-repeat;padding-left:32px;padding-right:8px;text-decoration-line:none;">Signin with Discord</a>

### server

1. Download the this repository.
1. Fix the `CLIENT_ID`, `CLIENT_SECRET`, `REDIRECT_URL` in `./.secret/config.json` 

### Shell

|Name|Value|
|-|-|
|Client ID|`1331215597119340585`|
|Client Secret|` `|
|Oauth URL|[OAuth2](https://discord.com/oauth2/authorize?client_id=1331215597119340585&response_type=code&redirect_uri=https%3A%2F%2Fn138-kz.github.io%2Fsso_discord%2F&scope=identify+email)|

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

```json
{
  "id": "0000000000000000",
  "username": "Your_name",
  "avatar": "00000000000000000000000000000000",
  "discriminator": "0",
  "public_flags": 0,
  "flags": 0,
  "banner": null,
  "accent_color": null,
  "global_name": null,
  "avatar_decoration_data": null,
  "banner_color": "#000000",
  "clan": null,
  "primary_guild": null,
  "mfa_enabled": true,
  "locale": "ja",
  "premium_type": 0,
  "email": "your@example.org",
  "verified": true
}

```
