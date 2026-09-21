# 出入厂管理系统（NXP Guard）

> **一句话总结**：这是一套基于 Yii2 开发的 NXP 厂区「出入厂管理系统」，以后台方式统一管理外部来访人员、来访车辆、长期包商的进出厂登记与放行，串联门禁刷卡（Lenel）、临时卡/车卡、海康人脸下发与蓝卡车牌等系统，并配套员工档案、菜单权限、报表导出等后台能力。

---

## 一、项目简介

- **业务定位**：厂区门岗场景下的"外部人员/车辆"进出厂全流程管理。内部正式员工（接待人/申请人）、门岗警卫分别通过系统完成登记、确认、发卡、进厂、出厂、还卡等操作。
- **框架**：Yii2 高级模板（`backend` 后台应用 + `common` 公共层 + `console` 控制台）。
- **数据来源**：自有业务库（MySQL `newguard`，表前缀 `nxp_`）+ 门禁原始库（SQL Server `ACCESSCONTROL`，Lenel OnGuard，连接名 `db2`，只读查询）。
- **前端**：服务端渲染的 PHP 视图 + Layui/X-admin 风格后台页面（jQuery + layui），无独立前端工程。

## 二、技术栈与运行环境

| 项目 | 说明 |
| --- | --- |
| 后端框架 | Yii2（`yiisoft/yii2` ~2.0.14） |
| PHP | >= 5.4（Docker 镜像使用 PHP 7.2 + Apache） |
| 业务数据库 | MySQL，库名 `newguard`，表前缀 `nxp_` |
| 门禁数据库 | SQL Server（`sqlsrv:Server=10.31.37.9;Database=ACCESSCONTROL`），连接名 `db2` |
| 主要依赖 | `yii2-bootstrap`、`yii2-swiftmailer`（邮件）、`moonlandsoft/yii2-phpexcel`（Excel）、`kartik-v/yii2-export`、`kartik-v/yii2-widget-select2`、`xj/yii2-qrcode-widget`（二维码）、`daixianceng/yii2-echarts` |
| UI 组件 | layui、X-admin、jSignature（手写签名）、lay-excel |
| 入口 | Web 根目录为 [backend/web](file:///d:/其他项目/NXP/归档/闫禹-进出管理系统/guard/guard/backend/web)，入口文件 [index.php](file:///d:/其他项目/NXP/归档/闫禹-进出管理系统/guard/guard/backend/web/index.php) |

## 三、功能模块

### 1. 外部来访人员进出厂管理（核心）

对应控制器 [ExteriorUserRecordController.php](file:///d:/其他项目/NXP/归档/闫禹-进出管理系统/guard/guard/backend/controllers/ExteriorUserRecordController.php)（系统内体量最大的控制器），是整套系统的业务主线。

- **来访人员档案**：外部人员（`ExteriorUser`）列表、详情、新增、编辑、删除，按手机号查询去重（重复申请、未录入手机号的校验）。
- **来访人员信息维护**：[ExteriorUserListController.php](file:///d:/其他项目/NXP/归档/闫禹-进出管理系统/guard/guard/backend/controllers/ExteriorUserListController.php)，列表 + 人员基础信息（姓名/电话/证件/公司）更新。
- **预约登记（提前申请）**：手机号登记 → 用户须知 → 填写补充问卷 → 生成登记记录。
- **现场登记**：无预约人员现场直接建档登记（含拍照）。
- **接待人通道**：接待人刷卡/PID 登记入口、接待人须知、接待人待办列表、按手机号登记。
- **门岗警卫通道**：警卫确认页 → 抓取刷卡信息 → 发放临时卡/车卡 → 放行进厂。
- **刷卡取数**：按卡机位置读取 Lenel `EVENTS` 刷卡流水，按卡类型（正式员工 / 包商可接待人 / 临时员工 / 车卡 / 警卫）区分并抓取 EMPID。
- **出厂登记**：出口扫卡 → 临时人员出厂登记 → 出厂放行（记录实际出厂时间与出厂警卫）。
- **特殊通道**：特殊通道登记与刷卡记录。
- **正式员工登记**：`personnel-add`（正式员工登记）与 `personnel-pid`（按 PID 登记/刷卡）。
- **其他**：二维码查看（进厂凭证）、手写签名、备注与"卡片丢失"登记、登记记录编辑/删除。
- **已完成查询**：[ExteriorUserAlreadyController.php](file:///d:/其他项目/NXP/归档/闫禹-进出管理系统/guard/guard/backend/controllers/ExteriorUserAlreadyController.php)，已出厂/卡片丢失记录的列表、详情与导出（含访客、公司、车牌、接待人、临时证号、进出厂时间等）。
- **可接待人白名单**：[ExteriorUserPeopleController.php](file:///d:/其他项目/NXP/归档/闫禹-进出管理系统/guard/guard/backend/controllers/ExteriorUserPeopleController.php)，维护哪些卡（Lenel `BADGE` 中 GUARD / AUTH 类型）可作为接待人，支持添加与删除。
- **部门/经理匹配校验**：依托关系表（`ExteriorRelation`）校验"申请人与接待人是否同分部/同经理"，并校验操作人是否为警卫角色。

### 2. 来访车辆 / 车卡管理

对应 [ExteriorUserCarController.php](file:///d:/其他项目/NXP/归档/闫禹-进出管理系统/guard/guard/backend/controllers/ExteriorUserCarController.php)。

- 来访车辆登记列表、车辆历史记录。
- 现场登记（车牌/PID 校验、重复申请校验、全国车牌号正则校验）。
- 抓取车卡与警卫卡刷卡信息，校验卡片归属厂区/门。
- 警卫确认 → 进厂 → 出厂登记 → 出厂放行，全流程与人员流程一致。
- 与**蓝卡车牌系统**联动：入厂/出厂时向车牌系统同步放行信息。

### 3. 长期包商管理（人脸门禁下发）

对应 [UserContractorController.php](file:///d:/其他项目/NXP/归档/闫禹-进出管理系统/guard/guard/backend/controllers/UserContractorController.php)，是独立于门岗登记的另一条业务线，走海康人脸平台。

- 包商人员建档、详情、编辑、删除（带事务回滚）。
- 人脸照片上传（超过 195KB 自动压缩）。
- **海康人脸门禁下发**：新增下发、更新下发（先删旧人脸再重新下发）、删除下发（同时删除人脸与设备权限）、批量下发、全部重新下发、重新下发（延期后）。
- **出入权限配置**：按组织 + 门禁设备配置放行权限。
- 批量导入人员数据、导入 EBdate 延期数据、下载导入模板、导出数据。
- 下发目标门禁点：前厅、东南门、北门（3 台设备）。

### 4. 临时卡 / 一次性卡管理

- **一次性卡**：[OneTimeCardController.php](file:///d:/其他项目/NXP/归档/闫禹-进出管理系统/guard/guard/backend/controllers/OneTimeCardController.php)，卡片列表、新增、编辑（区分东门/西门/南门/北门，状态含启用/禁用/借出）。
- **用户临时卡**：[UserCardController.php](file:///d:/其他项目/NXP/归档/闫禹-进出管理系统/guard/guard/backend/controllers/UserCardController.php)，维护账号与临时卡（EMPID）的绑定关系，列表/新增/编辑。
- **卡片记录与延期**：[CardRecordController.php](file:///d:/其他项目/NXP/归档/闫禹-进出管理系统/guard/guard/backend/controllers/CardRecordController.php)，临时卡延期、车卡延期、记录详情与处理（处理后置为已出厂）。
- **超期未还卡销卡**：[DelayController.php](file:///d:/其他项目/NXP/归档/闫禹-进出管理系统/guard/guard/backend/controllers/DelayController.php)，扫描正式进厂人员，进厂超过 24 小时仍未出厂的自动向门禁系统销卡。

### 5. 异常卡检测

对应 [AbnormalController.php](file:///d:/其他项目/NXP/归档/闫禹-进出管理系统/guard/guard/backend/controllers/AbnormalController.php)。

- 页面展示刷卡记录（CARD NAME / TIME / 刷卡门），前端每 3 秒自动刷新，可作实时刷卡监控。
- 后台比对 Lenel 刷卡流水与登记记录的出厂时间，识别"已出厂后再次刷卡"的异常行为并写入异常卡表。

### 6. Lenel 门禁数据报表与导出

- 旧版：[LenelExportController.php](file:///d:/其他项目/NXP/归档/闫禹-进出管理系统/guard/guard/backend/controllers/LenelExportController.php)，历史刷卡报表、区域报表、持卡人报表（其中历史/区域报表页已停用，导出接口仍保留）。
- 新版：[LenelExportNewController.php](file:///d:/其他项目/NXP/归档/闫禹-进出管理系统/guard/guard/backend/controllers/LenelExportNewController.php)，改用 `Local*` 镜像表查询，历史报表 / 区域报表 / 持卡人均可正常查询并导出 Excel。

### 7. 员工档案与系统账号

- **员工/用户档案**：[UserInfoController.php](file:///d:/其他项目/NXP/归档/闫禹-进出管理系统/guard/guard/backend/controllers/UserInfoController.php)，列表（姓名/PID/职位/部门号检索）、详情、新增、编辑、头像上传。
- **门禁数据同步**：从 Lenel `EMP` 批量同步员工到 `nxp_user_info`（含部门、卡类型），并自动生成登录账号与默认角色。
- **账号运维**：创建密码、修改密码（内置 admin 账号受保护）、启用/禁用、分配角色、导出 CSV、按名称搜索。
- **登录账号**：[UserController.php](file:///d:/其他项目/NXP/归档/闫禹-进出管理系统/guard/guard/backend/controllers/UserController.php)，账号列表与编辑。

### 8. 权限管理（RBAC）

采用「菜单 → 子菜单 → 路由 → 角色 → 用户角色」五级结构，菜单由数据库动态生成。

- **菜单/子菜单/路由维护**：[MenuController.php](file:///d:/其他项目/NXP/归档/闫禹-进出管理系统/guard/guard/backend/controllers/MenuController.php)，三类资源的增删改查与批量删除（存在下级时禁止删除）。
- **角色管理**：[RoleController.php](file:///d:/其他项目/NXP/归档/闫禹-进出管理系统/guard/guard/backend/controllers/RoleController.php)，角色新增/编辑（勾选路由权限，存为 JSON）、删除（超管、自身角色、有用户的角色不可删）。
- **鉴权**：[BaseController.php](file:///d:/其他项目/NXP/归档/闫禹-进出管理系统/guard/guard/backend/controllers/BaseController.php) 统一处理登录校验与路由权限校验，白名单为首页与清缓存；权限开关为常量 `RBAC`（当前为 `false`，即校验未启用），权限变更清缓存开关为 `RBAC_UPDATE`。
- **动态菜单**：[IndexController.php](file:///d:/其他项目/NXP/归档/闫禹-进出管理系统/guard/guard/backend/controllers/IndexController.php) 按当前用户角色聚合路由并组装左侧菜单（带缓存）。
- 业务中还存在按角色 ID 的硬编码校验，如警卫角色（`UserRole::GUARD_ROLE`）、超管 `role_id=1`、新建员工默认 `role_id=2`、临时卡延期详情可见角色 `[1,20,21,22]` 等。

### 9. 文件上传 / 拍照 / 签名

- **通用上传**：[UploadController.php](file:///d:/其他项目/NXP/归档/闫禹-进出管理系统/guard/guard/backend/controllers/UploadController.php)，文件上传（落 `FileInfo` 表并去重）、摄像头拍照（base64 PNG 落盘入库）、手写签名图片上传。
- **现场拍照**：[PhotoController.php](file:///d:/其他项目/NXP/归档/闫禹-进出管理系统/guard/guard/backend/controllers/PhotoController.php)，拍照页、按手机号暂存照片到 Session、按登记记录展示照片。
- 照片与登记记录通过 `ExteriorPhoto` 关联。

### 10. 来访人员情况补充确认（防疫问卷）

对应 [FormAnswerController.php](file:///d:/其他项目/NXP/归档/闫禹-进出管理系统/guard/guard/backend/controllers/FormAnswerController.php)。

- 来访人员情况补充确认问卷（10 项问答 + 手写签名），支持来访人填报与接待人填报两种入口，含中高风险地区等选项数据。

### 11. 表单文件记录

对应 [FormRecordController.php](file:///d:/其他项目/NXP/归档/闫禹-进出管理系统/guard/guard/backend/controllers/FormRecordController.php)，表单/承诺书等文件记录的增删改查，支持 PDF 在线预览与文件详情查看。

### 12. 批量导入

对应 [BatchImportController.php](file:///d:/其他项目/NXP/归档/闫禹-进出管理系统/guard/guard/backend/controllers/BatchImportController.php)。

- 来访记录批量导入（Excel），含必填校验、进厂/出厂时间跨度校验（≤30 天）、部门与工号解析、导入日志、导入结果页、模板下载、记录删除。

### 13. 系统与其他

- **登录/登出**：[SiteController.php](file:///d:/其他项目/NXP/归档/闫禹-进出管理系统/guard/guard/backend/controllers/SiteController.php)，账号密码校验 + 员工状态校验（停用账号禁止登录）。
- **后台首页/清缓存**：IndexController 的首页菜单与缓存清理。
- **测试接口**：[TestController.php](file:///d:/其他项目/NXP/归档/闫禹-进出管理系统/guard/guard/backend/controllers/TestController.php)，警卫邮件测试、车辆入厂接口联调（供开发调试使用）。

## 四、外部系统集成

| 集成对象 | 用途 | 主要位置 |
| --- | --- | --- |
| Lenel OnGuard（`db2`，SQL Server） | 读取员工、卡、部门、刷卡流水（`EMP`/`BADGE`/`EVENTS`/`UDFEMP`/`DEPT` 等） | [backend/models](file:///d:/其他项目/NXP/归档/闫禹-进出管理系统/guard/guard/backend/models) 下的 `Lenel*`、`Local*`、`LevelDept` 模型 |
| LenelWMIAdapter | 临时卡/车卡的发放、修改、销卡下发 | 来访登记、车辆、超期销卡等控制器内部方法 `addEditCard()` |
| 海康（Haikang）人脸门禁 | 长期包商人员与人脸的下发、更新、删除、权限配置 | [Haikang.php](file:///d:/其他项目/NXP/归档/闫禹-进出管理系统/guard/guard/common/models/Haikang.php) + `UserContractorController` |
| 蓝卡车牌系统 | 车辆入厂/出厂放行同步、心跳上报 | [LanKaCarController.php](file:///d:/其他项目/NXP/归档/闫禹-进出管理系统/guard/guard/backend/controllers/LanKaCarController.php) + 车辆控制器 |
| 邮件（SwiftMailer） | 通知邮件发送 | [SendEmail.php](file:///d:/其他项目/NXP/归档/闫禹-进出管理系统/guard/guard/common/models/SendEmail.php)、`BaseController::sendEmail()` |
| Excel / CSV | 批量导入与报表导出 | [Utility.php](file:///d:/其他项目/NXP/归档/闫禹-进出管理系统/guard/guard/common/models/Utility.php)、`BaseController::csvExport()` |
| FTP（预留） | 文件上传/下载能力 | [FtpUpdate.php](file:///d:/其他项目/NXP/归档/闫禹-进出管理系统/guard/guard/common/models/FtpUpdate.php) |
| 图片压缩 | 上传人脸照片超限时压缩 | [ImgCompress.php](file:///d:/其他项目/NXP/归档/闫禹-进出管理系统/guard/guard/common/models/ImgCompress.php) |

## 五、状态流转

登记记录（`ExteriorUserRecord`）状态：

```
待登记(1) → 警卫确认(2) → 已进厂(3) → 已出厂(4)
                              ↘ 卡片丢失(5)
```

- 申请方式：提前申请(1) / 现场申请(2)。
- 车辆记录（`ExteriorUserCar`）：待警卫确认(1) → 已进厂(2) / 警卫确认(3) → 已出厂(4) / 卡片丢失(5)。

## 六、目录结构

```
guard/
├── backend/                 # 后台应用
│   ├── controllers/         # 控制器（业务功能入口）
│   ├── models/              # 业务模型（Exterior* / User* / Menu / Role ...）
│   │   ├── search/          # 列表查询过滤器
│   │   └── serves/          # 业务服务层（登记核心逻辑）
│   ├── views/               # 视图（按模块分目录）
│   ├── widgets/             # 自定义控件（上传、下拉搜索、Layui 表格等）
│   ├── grids/               # GridView 列扩展
│   ├── behaviors/ actions/  # 上传行为与独立 Action
│   ├── config/              # 应用配置（main.php / params.php）
│   └── web/                 # Web 入口与静态资源
├── common/                  # 公共层
│   ├── config/              # 公共配置（数据库、邮件等）
│   └── models/              # 公共模型与工具（Haikang、FtpUpdate、Utility、Excel 等）
├── console/                 # 控制台应用
├── environments/            # 环境配置模板与 init 脚本
├── vendor/                  # Composer 依赖
├── docker-compose.yml       # 容器编排（模板自带）
└── requirements.php         # 环境检测
```

## 七、安装与部署

1. 安装依赖：

   ```bash
   composer install
   ```

2. 初始化环境（生成各应用配置文件）：

   ```bash
   php init        # Windows 使用 init.bat
   ```

3. 配置数据库与邮件（见 [main-local.php](file:///d:/其他项目/NXP/归档/闫禹-进出管理系统/guard/guard/common/config/main-local.php)）：
   - `db`：MySQL 业务库（`newguard`，前缀 `nxp_`）；
   - `db2`：SQL Server 门禁库（`ACCESSCONTROL`）；
   - `mailer`：SMTP 邮件发送账号。

4. 部署时把 Web 根目录指向 `backend/web`：
   - Nginx：`try_files $uri $uri/ /index.php?$args;`
   - Apache：参考 [backend/Dockerfile](file:///d:/其他项目/NXP/归档/闫禹-进出管理系统/guard/guard/backend/Dockerfile) 中修改 `DocumentRoot` 为 `/app/backend/web` 的做法。

5. 使用 Docker（可选，模板自带）：

   ```bash
   docker-compose up -d
   ```

## 八、已知问题与注意事项

- `backend/controllers.rar`、`backend/models.rar` 以及源码中若干 `xxx - 副本.php`、`register - 副本.php` 均为旧版本备份文件，不参与实际路由，可按需清理。
- [LenelExportNewController.php](file:///d:/其他项目/NXP/归档/闫禹-进出管理系统/guard/guard/backend/controllers/LenelExportNewController.php) 内部类名仍为 `LenelExportController`，与旧版控制器类名重复，命名不规范。
- 旧版 Lenel 报表的「历史报表」「区域报表」页面已硬编码停用（提示"数据升级中"），查询逻辑仍保留在旧版控制器中。
- 权限校验开关 `RBAC` 当前为 `false`，路由级鉴权未生效；登录校验仍然有效。
- 业务代码中存在多处硬编码的角色 ID、厂区/警卫白名单以及第三方系统密钥，维护或迁移时需一并确认。
