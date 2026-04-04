# Laravel Light API 项目文档

## 项目简介

Laravel Light API 是一个轻量级的 Laravel API 开发模板，专为快速构建 RESTful API 而设计。该项目移除了不必要的前端组件，专注于 API 开发，提供了完整的认证系统、CRUD 操作示例和详细的 API 文档，非常适合作为毕业设计或小程序后端开发的基础框架。

## 技术架构

### 核心技术栈

- **框架**: Laravel 10
- **认证**: Laravel Sanctum
- **API 文档**: Scribe
- **查询构建**: Spatie Query Builder
- **缓存**: Laravel 缓存系统
- **速率限制**: 自定义中间件
- **代码质量**: Laravel Pint
- **CI/CD**: GitHub Actions

### 架构设计

项目采用分层架构设计，主要包括以下层次：

1. **路由层**：处理 HTTP 请求，路由到对应控制器
2. **控制器层**：处理业务逻辑，调用模型和服务
3. **模型层**：定义数据结构和数据库关系
4. **策略层**：处理授权逻辑
5. **中间件层**：处理请求过滤和认证
6. **工具层**：提供通用功能，如统一响应格式

### 目录结构

```
app/
├── Http/
│   ├── Controllers/          # 控制器目录
│   │   ├── Api/              # API 控制器
│   │   └── Controller.php    # 基础控制器
│   ├── Middleware/           # 中间件目录
│   │   └── RateLimit.php     # 速率限制中间件
│   └── Kernel.php            # HTTP 内核
├── Models/                   # 模型目录
│   ├── User.php              # 用户模型
│   └── Post.php              # 示例文章模型
├── Policies/                 # 授权策略目录
│   └── PostPolicy.php        # 文章授权策略
└── Traits/                   #  traits 目录
    └── ApiResponse.php       # API 响应格式化

routes/
└── api.php                    # API 路由定义

database/
└── migrations/                # 数据库迁移文件

.github/workflows/
└── quality.yml                # GitHub Actions 工作流

.storage/app/scribe/           # API 文档生成文件
```

## 核心功能

### 1. 用户认证系统

- **注册**：创建新用户并返回访问令牌
- **登录**：验证用户凭据并返回访问令牌
- **登出**：撤销当前用户的访问令牌
- **获取当前用户**：返回当前认证用户的信息

### 2. 示例资源 (Post)

- **列表**：获取文章列表，支持过滤、排序和分页
- **创建**：创建新文章
- **详情**：获取单篇文章详情
- **更新**：更新文章信息
- **删除**：删除文章

### 3. API 文档

使用 Scribe 自动生成详细的 API 文档，包括：
- 端点描述
- 请求参数
- 响应示例
- 认证要求

访问地址：`http://localhost:8000/docs`

### 4. 性能优化

- **缓存机制**：对查询结果进行缓存，提高响应速度
- **速率限制**：防止 API 滥用，保护服务器资源
- **查询优化**：使用 Spatie Query Builder 优化数据库查询

## 部署指南

### 1. 共享主机部署

1. **上传文件**
   - 将项目文件上传到共享主机
   - 将 `public` 目录内容放置在网站根目录

2. **配置环境**
   - 基于 `.env.example` 创建 `.env` 文件
   - 设置数据库凭据和其他配置

3. **运行迁移**
   - 访问主机的 SSH 终端
   - 导航到项目目录
   - 运行 `php artisan migrate`

4. **优化配置**
   - 运行 `php artisan config:cache`
   - 运行 `php artisan route:cache`

### 2. VPS (Ubuntu) 部署

1. **安装依赖**
   ```bash
   sudo apt update
   sudo apt install php8.1 php8.1-cli php8.1-fpm php8.1-mysql php8.1-mbstring php8.1-xml php8.1-curl
   sudo apt install nginx mysql-server
   sudo apt install composer
   ```

2. **配置 Nginx**
   ```nginx
   # /etc/nginx/sites-available/laravel-api
   server {
       listen 80;
       server_name api.example.com;
       root /var/www/laravel-light-api/public;
       
       index index.php;
       
       location / {
           try_files $uri $uri/ /index.php?$query_string;
       }
       
       location ~ \.php$ {
           include snippets/fastcgi-php.conf;
           fastcgi_pass unix:/run/php/php8.1-fpm.sock;
       }
   }
   ```

3. **部署项目**
   ```bash
   cd /var/www
   git clone https://github.com/felix7word/laravel-light-api.git
   cd laravel-light-api
   composer install
   cp .env.example .env
   php artisan key:generate
   # 编辑 .env 文件
   php artisan migrate
   php artisan config:cache
   php artisan route:cache
   sudo chown -R www-data:www-data storage/
   ```

### 3. Docker 部署

1. **创建 Dockerfile**
   ```dockerfile
   # Dockerfile
   FROM php:8.1-fpm
   
   WORKDIR /var/www/html
   
   RUN apt-get update && apt-get install -y 
       libzip-dev 
       unzip 
       git 
       && docker-php-ext-install zip pdo_mysql
   
   RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
   
   COPY . .
   
   RUN composer install --no-dev --optimize-autoloader
   
   RUN php artisan key:generate
   
   EXPOSE 9000
   
   CMD ["php-fpm"]
   ```

2. **创建 docker-compose.yml**
   ```yaml
   version: '3'
   services:
     app:
       build: .
       volumes:
         - .:/var/www/html
       depends_on:
         - db
     db:
       image: mysql:8.0
       environment:
         MYSQL_ROOT_PASSWORD: root
         MYSQL_DATABASE: laravel_api
         MYSQL_USER: laravel
         MYSQL_PASSWORD: password
       ports:
         - "3306:3306"
     nginx:
       image: nginx:alpine
       volumes:
         - .:/var/www/html
         - ./nginx.conf:/etc/nginx/conf.d/default.conf
       ports:
         - "80:80"
       depends_on:
         - app
   ```

3. **创建 nginx.conf**
   ```nginx
   server {
       listen 80;
       server_name localhost;
       root /var/www/html/public;
       
       index index.php;
       
       location / {
           try_files $uri $uri/ /index.php?$query_string;
       }
       
       location ~ \.php$ {
           fastcgi_pass app:9000;
           fastcgi_index index.php;
           fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
           include fastcgi_params;
       }
   }
   ```

4. **运行容器**
   ```bash
   docker-compose up -d
   docker-compose exec app php artisan migrate
   ```

### 4. Heroku 部署

1. **创建 Heroku 应用**
   ```bash
   heroku create your-api-app
   ```

2. **设置数据库**
   ```bash
   heroku addons:create heroku-postgresql:mini
   ```

3. **配置环境**
   ```bash
   heroku config:set APP_KEY=$(php artisan key:generate --show)
   heroku config:set APP_ENV=production
   heroku config:set APP_DEBUG=false
   ```

4. **部署**
   ```bash
   git push heroku main
   heroku run php artisan migrate
   ```

## 适合毕设使用

### 1. 优势

- **快速上手**：提供了完整的 API 框架，无需从零开始搭建
- **功能完善**：包含用户认证、CRUD 操作、API 文档等核心功能
- **代码规范**：遵循 Laravel 最佳实践，代码结构清晰
- **扩展性强**：模块化设计，易于添加新功能
- **文档齐全**：自动生成 API 文档，便于展示和使用

### 2. 毕设应用场景

- **移动应用后端**：为 Android/iOS 应用提供 API 服务
- **Web 应用后端**：为前端框架（如 Vue、React）提供 API 服务
- **物联网项目**：为物联网设备提供数据接口
- **数据分析系统**：处理和提供数据分析 API
- **内容管理系统**：提供内容的 CRUD 操作 API

### 3. 如何扩展

1. **添加新资源**：
   - 创建新模型：`php artisan make:model Resource -m`
   - 创建控制器：`php artisan make:controller Api/ResourceController`
   - 添加路由：在 `routes/api.php` 中添加路由
   - 更新文档：`php artisan scribe:generate`

2. **添加新功能**：
   - 邮件服务：集成 Laravel Mail
   - 文件上传：添加文件存储功能
   - 支付集成：集成第三方支付 API
   - 推送通知：添加消息推送功能

## 适合小程序开发使用

### 1. 优势

- **轻量级**：移除了不必要的组件，响应速度快
- **认证简单**：使用 Sanctum 提供的令牌认证，适合小程序
- **API 友好**：RESTful 设计，易于小程序调用
- **文档完善**：自动生成的 API 文档便于小程序开发人员使用
- **部署灵活**：支持多种部署方式，适合不同规模的小程序项目

### 2. 小程序集成步骤

1. **获取访问令牌**：
   - 小程序调用 `/api/auth/login` 接口获取令牌
   - 将令牌存储在小程序的本地存储中

2. **API 调用**：
   - 在每个需要认证的 API 请求头中添加 `Authorization: Bearer {token}`
   - 处理 API 响应，统一错误处理

3. **示例代码（微信小程序）**：
   ```javascript
   // 登录
   wx.request({
     url: 'https://api.example.com/api/auth/login',
     method: 'POST',
     data: {
       email: 'user@example.com',
       password: 'password123'
     },
     success: function(res) {
       if (res.data.success) {
         wx.setStorageSync('token', res.data.data.access_token);
         wx.showToast({ title: '登录成功' });
       } else {
         wx.showToast({ title: '登录失败', icon: 'none' });
       }
     }
   });

   // 获取文章列表
   wx.request({
     url: 'https://api.example.com/api/posts',
     header: {
       'Authorization': 'Bearer ' + wx.getStorageSync('token')
     },
     success: function(res) {
       if (res.data.success) {
         console.log(res.data.data);
       } else {
         wx.showToast({ title: '获取失败', icon: 'none' });
       }
     }
   });
   ```

### 3. 小程序开发最佳实践

- **令牌管理**：安全存储令牌，定期刷新
- **错误处理**：统一处理 API 错误，提供友好的用户提示
- **网络请求**：使用封装的请求方法，处理认证和错误
- **数据缓存**：在小程序端缓存常用数据，减少 API 请求
- **用户体验**：添加加载状态，优化网络请求体验

## 快速开始

### 1. 环境要求

- PHP >= 8.1
- Composer
- MySQL 或 SQLite

### 2. 安装步骤

1. **克隆项目**
   ```bash
   git clone https://github.com/felix7word/laravel-light-api.git
   cd laravel-light-api
   ```

2. **安装依赖**
   ```bash
   composer install
   ```

3. **配置环境**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   编辑 `.env` 文件，设置数据库连接信息

4. **运行迁移**
   ```bash
   php artisan migrate
   ```

5. **启动开发服务器**
   ```bash
   php artisan serve
   ```

6. **访问 API 文档**
   打开浏览器访问：`http://localhost:8000/docs`

## 示例 API 调用

### 1. 注册用户

```bash
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{"name": "张三", "email": "zhangsan@example.com", "password": "password123"}'
```

### 2. 登录

```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email": "zhangsan@example.com", "password": "password123"}'
```

### 3. 创建文章

```bash
curl -X POST http://localhost:8000/api/posts \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -d '{"title": "Laravel API 开发", "content": "使用 Laravel 构建 RESTful API"}'
```

### 4. 获取文章列表

```bash
curl -X GET "http://localhost:8000/api/posts?filter[title]=Laravel&sort=-created_at" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN"
```

## 注意事项

1. **安全性**：
   - 生产环境中务必设置强密码
   - 启用 HTTPS 保护 API 通信
   - 定期更新依赖包，修复安全漏洞

2. **性能**：
   - 合理使用缓存，避免频繁查询数据库
   - 优化数据库查询，添加适当的索引
   - 配置合适的速率限制，防止 API 滥用

3. **维护**：
   - 定期备份数据库
   - 监控 API 运行状态
   - 记录关键操作日志

4. **扩展性**：
   - 遵循 Laravel 最佳实践，保持代码结构清晰
   - 使用服务层分离业务逻辑，提高代码可维护性
   - 编写单元测试，确保代码质量

## 总结

Laravel Light API 是一个功能完善、易于使用的 API 开发模板，特别适合作为毕业设计或小程序后端开发的基础框架。它提供了完整的认证系统、CRUD 操作示例、API 文档生成和性能优化功能，同时保持了轻量级的特点。通过本项目，你可以快速搭建 API 服务，专注于业务逻辑的实现，提高开发效率，为毕设或小程序项目提供稳定可靠的后端支持。