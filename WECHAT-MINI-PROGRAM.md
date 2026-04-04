# 小程序与 Laravel Light API 集成指南

本指南将详细说明如何将 Laravel Light API 作为微信小程序的后端服务，包括认证流程、API 调用、错误处理等方面的内容，帮助开发者快速上手。

## 目录

- [准备工作](#准备工作)
- [认证流程](#认证流程)
- [API 调用示例](#api-调用示例)
- [错误处理](#错误处理)
- [最佳实践](#最佳实践)
- [部署建议](#部署建议)
- [常见问题](#常见问题)

## 准备工作

### 1. 后端准备

1. **克隆并配置 Laravel Light API**
   ```bash
   git clone https://github.com/yourusername/laravel-light-api.git
   cd laravel-light-api
   composer install
   cp .env.example .env
   php artisan key:generate
   # 编辑 .env 文件，设置数据库连接
   php artisan migrate
   php artisan serve
   ```

2. **获取 API 文档**
   打开浏览器访问：`http://localhost:8000/docs`，查看完整的 API 文档

### 2. 小程序准备

1. **创建微信小程序**
   - 登录 [微信公众平台](https://mp.weixin.qq.com/)
   - 创建新的小程序项目
   - 记录小程序的 AppID

2. **配置小程序开发环境**
   - 下载并安装 [微信开发者工具](https://developers.weixin.qq.com/miniprogram/dev/devtools/download.html)
   - 使用 AppID 登录开发者工具
   - 创建新的小程序项目

3. **配置网络请求**
   - 在小程序管理后台，进入「开发」->「开发设置」->「服务器域名」
   - 添加后端 API 域名到「request 合法域名」中
   - 开发阶段可以在开发者工具中开启「不校验合法域名」选项

## 认证流程

### 1. 注册新用户

小程序端代码示例：

```javascript
// pages/register/register.js
Page({
  data: {
    name: '',
    email: '',
    password: ''
  },
  
  // 注册方法
  register() {
    wx.showLoading({ title: '注册中...' });
    
    wx.request({
      url: 'http://localhost:8000/api/auth/register',
      method: 'POST',
      data: {
        name: this.data.name,
        email: this.data.email,
        password: this.data.password
      },
      success: (res) => {
        wx.hideLoading();
        
        if (res.data.success) {
          // 注册成功，保存 token
          wx.setStorageSync('token', res.data.data.access_token);
          wx.setStorageSync('user', res.data.data.user);
          
          wx.showToast({ 
            title: '注册成功',
            icon: 'success'
          });
          
          // 跳转到首页
          wx.switchTab({ url: '/pages/index/index' });
        } else {
          wx.showToast({ 
            title: '注册失败: ' + res.data.message,
            icon: 'none'
          });
        }
      },
      fail: (err) => {
        wx.hideLoading();
        wx.showToast({ 
          title: '网络错误',
          icon: 'none'
        });
      }
    });
  }
});
```

### 2. 用户登录

小程序端代码示例：

```javascript
// pages/login/login.js
Page({
  data: {
    email: '',
    password: ''
  },
  
  // 登录方法
  login() {
    wx.showLoading({ title: '登录中...' });
    
    wx.request({
      url: 'http://localhost:8000/api/auth/login',
      method: 'POST',
      data: {
        email: this.data.email,
        password: this.data.password
      },
      success: (res) => {
        wx.hideLoading();
        
        if (res.data.success) {
          // 登录成功，保存 token
          wx.setStorageSync('token', res.data.data.access_token);
          wx.setStorageSync('user', res.data.data.user);
          
          wx.showToast({ 
            title: '登录成功',
            icon: 'success'
          });
          
          // 跳转到首页
          wx.switchTab({ url: '/pages/index/index' });
        } else {
          wx.showToast({ 
            title: '登录失败: ' + res.data.message,
            icon: 'none'
          });
        }
      },
      fail: (err) => {
        wx.hideLoading();
        wx.showToast({ 
          title: '网络错误',
          icon: 'none'
        });
      }
    });
  }
});
```

### 3. 获取当前用户信息

小程序端代码示例：

```javascript
// pages/profile/profile.js
Page({
  data: {
    user: null
  },
  
  onLoad() {
    this.getUserInfo();
  },
  
  // 获取用户信息
  getUserInfo() {
    const token = wx.getStorageSync('token');
    
    if (!token) {
      wx.showToast({ 
        title: '请先登录',
        icon: 'none'
      });
      wx.navigateTo({ url: '/pages/login/login' });
      return;
    }
    
    wx.showLoading({ title: '加载中...' });
    
    wx.request({
      url: 'http://localhost:8000/api/auth/user',
      method: 'GET',
      header: {
        'Authorization': 'Bearer ' + token
      },
      success: (res) => {
        wx.hideLoading();
        
        if (res.data.success) {
          this.setData({ user: res.data.data });
        } else {
          wx.showToast({ 
            title: '获取用户信息失败',
            icon: 'none'
          });
        }
      },
      fail: (err) => {
        wx.hideLoading();
        wx.showToast({ 
          title: '网络错误',
          icon: 'none'
        });
      }
    });
  }
});
```

### 4. 用户登出

小程序端代码示例：

```javascript
// pages/profile/profile.js
Page({
  // 登出方法
  logout() {
    const token = wx.getStorageSync('token');
    
    if (!token) {
      wx.showToast({ 
        title: '请先登录',
        icon: 'none'
      });
      return;
    }
    
    wx.showLoading({ title: '登出中...' });
    
    wx.request({
      url: 'http://localhost:8000/api/auth/logout',
      method: 'POST',
      header: {
        'Authorization': 'Bearer ' + token
      },
      success: (res) => {
        wx.hideLoading();
        
        // 清除本地存储
        wx.removeStorageSync('token');
        wx.removeStorageSync('user');
        
        wx.showToast({ 
          title: '登出成功',
          icon: 'success'
        });
        
        // 跳转到登录页
        wx.redirectTo({ url: '/pages/login/login' });
      },
      fail: (err) => {
        wx.hideLoading();
        // 即使网络错误也清除本地存储
        wx.removeStorageSync('token');
        wx.removeStorageSync('user');
        wx.showToast({ 
          title: '登出成功',
          icon: 'success'
        });
        wx.redirectTo({ url: '/pages/login/login' });
      }
    });
  }
});
```

## API 调用示例

### 1. 获取文章列表

小程序端代码示例：

```javascript
// pages/posts/posts.js
Page({
  data: {
    posts: [],
    loading: false,
    page: 1,
    hasMore: true
  },
  
  onLoad() {
    this.loadPosts();
  },
  
  // 加载文章列表
  loadPosts() {
    if (this.data.loading || !this.data.hasMore) return;
    
    const token = wx.getStorageSync('token');
    
    if (!token) {
      wx.showToast({ 
        title: '请先登录',
        icon: 'none'
      });
      wx.navigateTo({ url: '/pages/login/login' });
      return;
    }
    
    this.setData({ loading: true });
    
    wx.request({
      url: `http://localhost:8000/api/posts?page=${this.data.page}`,
      method: 'GET',
      header: {
        'Authorization': 'Bearer ' + token
      },
      success: (res) => {
        this.setData({ loading: false });
        
        if (res.data.success) {
          const newPosts = res.data.data;
          const posts = this.data.page === 1 ? newPosts : [...this.data.posts, ...newPosts];
          
          this.setData({
            posts: posts,
            hasMore: newPosts.length === 10, // 假设每页10条
            page: this.data.page + 1
          });
        } else {
          wx.showToast({ 
            title: '获取文章失败',
            icon: 'none'
          });
        }
      },
      fail: (err) => {
        this.setData({ loading: false });
        wx.showToast({ 
          title: '网络错误',
          icon: 'none'
        });
      }
    });
  },
  
  // 下拉刷新
  onPullDownRefresh() {
    this.setData({ page: 1, hasMore: true });
    this.loadPosts();
    wx.stopPullDownRefresh();
  },
  
  // 上拉加载更多
  onReachBottom() {
    this.loadPosts();
  }
});
```

### 2. 创建文章

小程序端代码示例：

```javascript
// pages/create-post/create-post.js
Page({
  data: {
    title: '',
    content: ''
  },
  
  // 创建文章
  createPost() {
    const token = wx.getStorageSync('token');
    
    if (!token) {
      wx.showToast({ 
        title: '请先登录',
        icon: 'none'
      });
      wx.navigateTo({ url: '/pages/login/login' });
      return;
    }
    
    if (!this.data.title || !this.data.content) {
      wx.showToast({ 
        title: '请填写标题和内容',
        icon: 'none'
      });
      return;
    }
    
    wx.showLoading({ title: '发布中...' });
    
    wx.request({
      url: 'http://localhost:8000/api/posts',
      method: 'POST',
      header: {
        'Authorization': 'Bearer ' + token,
        'Content-Type': 'application/json'
      },
      data: {
        title: this.data.title,
        content: this.data.content
      },
      success: (res) => {
        wx.hideLoading();
        
        if (res.data.success) {
          wx.showToast({ 
            title: '发布成功',
            icon: 'success'
          });
          
          // 跳转到文章列表页
          wx.navigateBack();
        } else {
          wx.showToast({ 
            title: '发布失败: ' + res.data.message,
            icon: 'none'
          });
        }
      },
      fail: (err) => {
        wx.hideLoading();
        wx.showToast({ 
          title: '网络错误',
          icon: 'none'
        });
      }
    });
  }
});
```

### 3. 更新文章

小程序端代码示例：

```javascript
// pages/edit-post/edit-post.js
Page({
  data: {
    postId: '',
    title: '',
    content: ''
  },
  
  onLoad(options) {
    this.setData({ postId: options.id });
    this.loadPost();
  },
  
  // 加载文章详情
  loadPost() {
    const token = wx.getStorageSync('token');
    
    if (!token) {
      wx.showToast({ 
        title: '请先登录',
        icon: 'none'
      });
      wx.navigateTo({ url: '/pages/login/login' });
      return;
    }
    
    wx.showLoading({ title: '加载中...' });
    
    wx.request({
      url: `http://localhost:8000/api/posts/${this.data.postId}`,
      method: 'GET',
      header: {
        'Authorization': 'Bearer ' + token
      },
      success: (res) => {
        wx.hideLoading();
        
        if (res.data.success) {
          this.setData({
            title: res.data.data.title,
            content: res.data.data.content
          });
        } else {
          wx.showToast({ 
            title: '获取文章失败',
            icon: 'none'
          });
        }
      },
      fail: (err) => {
        wx.hideLoading();
        wx.showToast({ 
          title: '网络错误',
          icon: 'none'
        });
      }
    });
  },
  
  // 更新文章
  updatePost() {
    const token = wx.getStorageSync('token');
    
    if (!token) {
      wx.showToast({ 
        title: '请先登录',
        icon: 'none'
      });
      wx.navigateTo({ url: '/pages/login/login' });
      return;
    }
    
    if (!this.data.title || !this.data.content) {
      wx.showToast({ 
        title: '请填写标题和内容',
        icon: 'none'
      });
      return;
    }
    
    wx.showLoading({ title: '更新中...' });
    
    wx.request({
      url: `http://localhost:8000/api/posts/${this.data.postId}`,
      method: 'PUT',
      header: {
        'Authorization': 'Bearer ' + token,
        'Content-Type': 'application/json'
      },
      data: {
        title: this.data.title,
        content: this.data.content
      },
      success: (res) => {
        wx.hideLoading();
        
        if (res.data.success) {
          wx.showToast({ 
            title: '更新成功',
            icon: 'success'
          });
          
          // 跳转到文章详情页
          wx.navigateBack();
        } else {
          wx.showToast({ 
            title: '更新失败: ' + res.data.message,
            icon: 'none'
          });
        }
      },
      fail: (err) => {
        wx.hideLoading();
        wx.showToast({ 
          title: '网络错误',
          icon: 'none'
        });
      }
    });
  }
});
```

### 4. 删除文章

小程序端代码示例：

```javascript
// pages/post-detail/post-detail.js
Page({
  data: {
    post: null
  },
  
  // 删除文章
  deletePost() {
    const token = wx.getStorageSync('token');
    const postId = this.data.post.id;
    
    if (!token) {
      wx.showToast({ 
        title: '请先登录',
        icon: 'none'
      });
      wx.navigateTo({ url: '/pages/login/login' });
      return;
    }
    
    wx.showModal({
      title: '确认删除',
      content: '确定要删除这篇文章吗？',
      success: (res) => {
        if (res.confirm) {
          wx.showLoading({ title: '删除中...' });
          
          wx.request({
            url: `http://localhost:8000/api/posts/${postId}`,
            method: 'DELETE',
            header: {
              'Authorization': 'Bearer ' + token
            },
            success: (res) => {
              wx.hideLoading();
              
              if (res.data.success) {
                wx.showToast({ 
                  title: '删除成功',
                  icon: 'success'
                });
                
                // 跳转到文章列表页
                wx.navigateBack();
              } else {
                wx.showToast({ 
                  title: '删除失败: ' + res.data.message,
                  icon: 'none'
                });
              }
            },
            fail: (err) => {
              wx.hideLoading();
              wx.showToast({ 
                title: '网络错误',
                icon: 'none'
              });
            }
          });
        }
      }
    });
  }
});
```

## 错误处理

### 1. 统一错误处理

创建一个 `request.js` 文件，封装网络请求并统一处理错误：

```javascript
// utils/request.js
const baseUrl = 'http://localhost:8000/api';

const request = (url, options = {}) => {
  // 获取 token
  const token = wx.getStorageSync('token');
  
  // 设置默认选项
  options.url = baseUrl + url;
  options.header = {
    'Content-Type': 'application/json',
    ...options.header
  };
  
  // 添加认证头
  if (token) {
    options.header.Authorization = 'Bearer ' + token;
  }
  
  // 返回 Promise
  return new Promise((resolve, reject) => {
    wx.request({
      ...options,
      success: (res) => {
        if (res.statusCode === 200) {
          if (res.data.success) {
            resolve(res.data);
          } else {
            // 业务错误
            reject(new Error(res.data.message || '请求失败'));
          }
        } else if (res.statusCode === 401) {
          // 未授权，清除 token 并跳转到登录页
          wx.removeStorageSync('token');
          wx.removeStorageSync('user');
          wx.showToast({ 
            title: '登录已过期，请重新登录',
            icon: 'none'
          });
          wx.navigateTo({ url: '/pages/login/login' });
          reject(new Error('登录已过期'));
        } else if (res.statusCode === 429) {
          // 速率限制
          wx.showToast({ 
            title: '请求过于频繁，请稍后再试',
            icon: 'none'
          });
          reject(new Error('请求过于频繁'));
        } else {
          // 其他错误
          reject(new Error(`请求失败: ${res.statusCode}`));
        }
      },
      fail: (err) => {
        wx.showToast({ 
          title: '网络错误',
          icon: 'none'
        });
        reject(new Error('网络错误'));
      }
    });
  });
};

// 导出方法
export default {
  get: (url, options = {}) => request(url, { ...options, method: 'GET' }),
  post: (url, data, options = {}) => request(url, { ...options, method: 'POST', data }),
  put: (url, data, options = {}) => request(url, { ...options, method: 'PUT', data }),
  delete: (url, options = {}) => request(url, { ...options, method: 'DELETE' })
};
```

### 2. 使用封装的请求方法

```javascript
// pages/posts/posts.js
import request from '../../utils/request';

Page({
  data: {
    posts: [],
    loading: false
  },
  
  onLoad() {
    this.loadPosts();
  },
  
  async loadPosts() {
    if (this.data.loading) return;
    
    this.setData({ loading: true });
    
    try {
      const response = await request.get('/posts');
      this.setData({ posts: response.data });
    } catch (error) {
      console.error('加载文章失败:', error);
    } finally {
      this.setData({ loading: false });
    }
  }
});
```

## 最佳实践

### 1. 状态管理

对于复杂的小程序，建议使用状态管理库（如 Redux、MobX 或微信官方的 MobX 实现）来管理全局状态，特别是用户信息和认证状态。

### 2. 数据缓存

- **本地缓存**：使用 `wx.setStorageSync` 和 `wx.getStorageSync` 缓存用户信息和令牌
- **API 缓存**：对于不经常变化的数据，使用 `wx.setStorageSync` 缓存 API 响应
- **缓存策略**：实现合理的缓存过期机制，避免数据过期

### 3. 网络优化

- **请求合并**：合并多个相关请求，减少网络请求次数
- **批量操作**：对于批量删除、批量更新等操作，使用批量 API 接口
- **懒加载**：对于列表数据，实现分页加载和下拉刷新
- **预加载**：预加载可能需要的数据，提高用户体验

### 4. 安全性

- **HTTPS**：生产环境中使用 HTTPS 加密通信
- **令牌管理**：安全存储令牌，定期刷新
- **输入验证**：在小程序端进行基本的输入验证，减轻服务器负担
- **防 CSRF**：实现 CSRF 保护机制

### 5. 用户体验

- **加载状态**：添加加载动画，提升用户体验
- **错误提示**：提供清晰的错误提示，帮助用户理解问题
- **离线模式**：实现基本的离线模式，提高应用可靠性
- **响应式设计**：适配不同尺寸的设备

## 部署建议

### 1. 后端部署

- **生产环境**：使用 VPS 或云服务器部署 Laravel API
- **数据库**：使用 MySQL 或 PostgreSQL
- **缓存**：配置 Redis 作为缓存和会话存储
- **HTTPS**：配置 SSL 证书，启用 HTTPS
- **负载均衡**：对于高流量应用，配置负载均衡

### 2. 小程序部署

- **域名配置**：在微信公众平台配置合法的 API 域名
- **审核**：确保小程序符合微信的审核规范
- **版本管理**：使用版本控制管理小程序代码
- **灰度发布**：实现灰度发布，逐步推出新功能

### 3. 监控和日志

- **API 监控**：使用监控工具监控 API 性能和错误
- **日志记录**：在服务器端记录详细的 API 日志
- **错误追踪**：使用错误追踪工具（如 Sentry）监控错误
- **性能分析**：定期分析 API 性能，优化慢查询

## 常见问题

### 1. 跨域问题

**问题**：小程序请求 API 时出现跨域错误

**解决方案**：
- 在 Laravel 中配置 CORS 中间件
- 在 `.env` 文件中设置 `APP_URL` 为实际域名
- 在小程序管理后台添加合法域名

### 2. 令牌过期

**问题**：使用一段时间后，API 请求返回 401 错误

**解决方案**：
- 实现令牌刷新机制
- 在 `request.js` 中捕获 401 错误，自动跳转到登录页
- 定期检查令牌状态，提前刷新

### 3. 网络请求失败

**问题**：小程序网络请求经常失败

**解决方案**：
- 检查网络连接
- 确保 API 服务器正常运行
- 实现请求重试机制
- 使用 CDN 加速静态资源

### 4. 性能问题

**问题**：API 响应缓慢

**解决方案**：
- 优化数据库查询，添加适当的索引
- 使用缓存机制
- 实现分页加载
- 优化 API 响应大小，只返回必要的数据

### 5. 安全性问题

**问题**：担心 API 被恶意调用

**解决方案**：
- 实现速率限制
- 使用 HTTPS
- 验证请求来源
- 实现 API 密钥验证

## 总结

本指南详细介绍了如何将 Laravel Light API 作为微信小程序的后端服务，包括认证流程、API 调用、错误处理和最佳实践。通过遵循这些步骤，开发者可以快速搭建一个安全、高效的小程序后端系统，提供良好的用户体验。

Laravel Light API 提供了完整的 API 功能和文档，使小程序开发变得更加简单和高效。开发者可以根据实际需求扩展 API 功能，构建更加复杂的应用。

希望本指南能够帮助你快速上手小程序与 Laravel API 的集成开发！