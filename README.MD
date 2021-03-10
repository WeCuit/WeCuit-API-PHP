### Files Structure

```
wecuit
│
└───application           – 应用代码
|    |
|    └──config            – 程序配置或数据库配置
|    |     | config.php
|    |
|    └──controllers       - 控制器
|    |     |
|    |     └──Api
|    |     |
|    |     └──Admin
|    |
|    └──models            - 模型
|    |
|    └──views             - 视图
|          |
|          └──Api
|          |
|          └──Admin
|
└───wecuit                - 框架核心目录
|    │
|    └───core             - 框架核心文件目录
|    │
|    └───database         - 数据库目录（比如数据库启动类）
|    │
|    └───helpers          - 辅助函数目录
|    |
|    └───libraries        - 类库目录
|
└───public                – 静态文件
|
└───runtime               - 临时数据目录
|    |
|    └───caches           - 缓存数据目录
|    |
|    └───logs             - 日志目录
│
└───scripts               – 命令行工具

```

# 状态码说明

| 状态码 | 类型           |
| ------ | -------------- |
| 2000   | 一切正常       |
| 10xxx  | 系统相关错误码 |
| 11xxx  | webvpn 相关    |
| 12xxx  | SSO 相关错误码 |
| 13xxx  | 教务系统相关   |
| 17xxx  | 图书馆相关     |
| 18xxx  | 用户相关       |
| 19xxx  | 资讯相关       |
| 20xxx  | 计算中心相关   |
| 21xxx  | 教学平台相关   |

### 10xxx
10511  服务器网络异常
10503  服务器维护
10512  验证码识别出错