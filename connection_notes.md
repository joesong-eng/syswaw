# 連線配置筆記

## Mosquitto 配置路徑
`/www/wwwroot/syswaw/mosquitto` 實際上對應到系統中的 `/etc/mosquitto` 目錄。

## Nginx 反向代理配置
以下是 Nginx 反向代理的配置，用於將 `/mqtt` 和 `/mqtt/` 路徑代理到 `http://127.0.0.1:9001`：

```nginx
location /mqtt {
    proxy_pass http://127.0.0.1:9001;
    proxy_http_version 1.1;
    proxy_set_header Upgrade $http_upgrade;
    proxy_set_header Connection "upgrade";
    proxy_set_header Host $host;
    proxy_set_header X-Real-IP $remote_addr;
}
location /mqtt/ {
    proxy_pass http://127.0.0.1:9001/;
    proxy_http_version 1.1;
    proxy_set_header Upgrade $http_upgrade;
    proxy_set_header Connection "upgrade";
    proxy_set_header Host $host;
    proxy_set_header X-Real-IP $remote_addr;
}
