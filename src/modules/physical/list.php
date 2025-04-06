<?php
require_once '../../includes/auth_check.php';
require_once '../../config/database.php';

$error = '';
$user = null;
$records = [];

if (isset($_GET['user_id'])) {
    $database = new Database();
    $db = $database->getConnection();
    
    try {
        // 获取用户基本信息
        $stmt = $db->prepare("SELECT * FROM demographics WHERE id = ?");
        $stmt->execute([$_GET['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // 获取健康记录
        $stmt = $db->prepare("
            SELECT p.*, u.name as examiner_name 
            FROM physical_exams p
            LEFT JOIN users u ON p.examiner_id = u.id
            WHERE p.user_id = ? 
            ORDER BY p.exam_date DESC
        ");
        $stmt->execute([$_GET['user_id']]);
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        $error = "获取信息失败：" . $e->getMessage();
    }
}

if (!$user) {
    header('Location: ../users/search.php');
    exit;
}

// 计算年龄
$age = '';
if (!empty($user['birth_date'])) {
    $birthDate = new DateTime($user['birth_date']);
    $today = new DateTime('today');
    $age = $birthDate->diff($today)->y;
}

require_once '../../includes/header.php';
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
<style>
    .page-header {
        background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
        color: white;
        padding: 2rem 0;
        margin-bottom: 2rem;
        border-radius: 0 0 15px 15px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    .user-info {
        display: flex;
        align-items: center;
    }
    .user-avatar {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background-color: #fff;
        color: #4e73df;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        margin-right: 1rem;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }
    .user-name {
        font-size: 1.5rem;
        font-weight: 600;
    }
    .user-details {
        font-size: 0.9rem;
        opacity: 0.9;
    }
    .card {
        border: none;
        border-radius: 10px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        margin-bottom: 2rem;
        overflow: hidden;
    }
    .card-header {
        background-color: #f8f9fa;
        border-bottom: none;
        padding: 1rem 1.5rem;
        font-weight: 600;
    }
    .btn-action {
        border-radius: 50px;
        padding: 0.375rem 1.2rem;
    }
    .table-container {
        padding: 1rem;
    }
    .table {
        width: 100%;
        margin-bottom: 0;
    }
    .table th {
        font-weight: 600;
        color: #495057;
        border-top: none;
    }
    .table td {
        vertical-align: middle;
    }
    .badge-status {
        padding: 0.5rem 0.8rem;
        font-size: 0.85rem;
        border-radius: 50px;
    }
    .action-buttons .btn {
        margin-right: 0.5rem;
    }
    .action-buttons .btn:last-child {
        margin-right: 0;
    }
    .empty-state {
        text-align: center;
        padding: 3rem 1rem;
    }
    .empty-icon {
        font-size: 4rem;
        color: #dee2e6;
        margin-bottom: 1rem;
    }
    .tab-content {
        padding: 1.5rem;
    }
    .nav-tabs {
        border-bottom: none;
        padding: 0 1rem;
    }
    .nav-tabs .nav-link {
        border: none;
        border-radius: 50px;
        padding: 0.5rem 1.5rem;
        margin-right: 0.5rem;
        color: #6c757d;
        font-weight: 500;
    }
    .nav-tabs .nav-link.active {
        background-color: #4e73df;
        color: white;
    }
    .nav-tabs .nav-link:hover:not(.active) {
        background-color: #f8f9fa;
    }
    .health-summary {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        margin-bottom: 1.5rem;
    }
    .summary-item {
        flex: 1;
        min-width: 200px;
        background-color: #f8f9fa;
        border-radius: 10px;
        padding: 1rem;
        text-align: center;
    }
    .summary-icon {
        font-size: 2rem;
        margin-bottom: 0.5rem;
    }
    .summary-value {
        font-size: 1.5rem;
        font-weight: 600;
    }
    .summary-label {
        font-size: 0.9rem;
        color: #6c757d;
    }
    .dataTables_wrapper .dataTables_length, 
    .dataTables_wrapper .dataTables_filter {
        margin-bottom: 1rem;
    }
    .dataTables_wrapper .dataTables_info, 
    .dataTables_wrapper .dataTables_paginate {
        margin-top: 1rem;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button {
        border-radius: 50px;
        padding: 0.3rem 0.8rem;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: #4e73df;
        border-color: #4e73df;
        color: white !important;
    }
</style>

<div class="page-header">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center">
            <div class="user-info">
                <div class="user-avatar">
                    <?php 
                    $genderIcon = 'person';
                    if ($user['gender'] == '男') {
                        $genderIcon = 'gender-male';
                    } elseif ($user['gender'] == '女') {
                        $genderIcon = 'gender-female';
                    }
                    ?>
                    <i class="bi bi-<?php echo $genderIcon; ?>"></i>
                </div>
                <div>
                    <div class="user-name"><?php echo htmlspecialchars($user['name']); ?></div>
                    <div class="user-details">
                        <span class="me-2"><i class="bi bi-telephone me-1"></i><?php echo htmlspecialchars($user['phone'] ?? '未设置'); ?></span>
                        <?php if ($age): ?>
                            <span class="me-2"><i class="bi bi-person-badge me-1"></i><?php echo $age; ?>岁</span>
                        <?php endif; ?>
                        <span><i class="bi bi-<?php echo $genderIcon; ?> me-1"></i><?php echo htmlspecialchars($user['gender']); ?></span>
                    </div>
                </div>
            </div>
            <div>
                <a href="../users/view.php?id=<?php echo $user['id']; ?>" class="btn btn-outline-light btn-action">
                    <i class="bi bi-person me-1"></i>用户详情
                </a>
                <a href="../users/search.php" class="btn btn-outline-light btn-action ms-2">
                    <i class="bi bi-arrow-left me-1"></i>返回列表
                </a>
            </div>
        </div>
    </div>
</div>

<div class="container">
    <?php if ($error): ?>
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <i class="bi bi-clipboard2-pulse me-2"></i>健康记录管理
            </div>
            <a href="create.php?user_id=<?php echo $user['id']; ?>" class="btn btn-primary btn-action">
                <i class="bi bi-plus-circle me-1"></i>添加新记录
            </a>
        </div>
        
        <ul class="nav nav-tabs mt-3" id="healthTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="records-tab" data-bs-toggle="tab" data-bs-target="#records" type="button" role="tab" aria-controls="records" aria-selected="true">
                    <i class="bi bi-list-ul me-1"></i>记录列表
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="summary-tab" data-bs-toggle="tab" data-bs-target="#summary" type="button" role="tab" aria-controls="summary" aria-selected="false">
                    <i class="bi bi-graph-up me-1"></i>健康概览
                </button>
            </li>
        </ul>
        
        <div class="tab-content" id="healthTabsContent">
            <div class="tab-pane fade show active" id="records" role="tabpanel" aria-labelledby="records-tab">
                <?php if (count($records) > 0): ?>
                    <div class="table-container">
                        <table id="healthRecordsTable" class="table table-hover">
                            <thead>
                                <tr>
                                    <th>检查日期</th>
                                    <th>检查类型</th>
                                    <th>检查医生</th>
                                    <th>主要发现</th>
                                    <th>状态</th>
                                    <th>操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($records as $record): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($record['exam_date']); ?></td>
                                        <td><?php echo htmlspecialchars($record['exam_type']); ?></td>
                                        <td><?php echo htmlspecialchars($record['examiner_name'] ?? '未记录'); ?></td>
                                        <td>
                                            <?php 
                                            $findings = htmlspecialchars($record['findings'] ?? '');
                                            echo strlen($findings) > 50 ? substr($findings, 0, 50) . '...' : $findings; 
                                            ?>
                                        </td>
                                        <td>
                                            <?php 
                                            $status = htmlspecialchars($record['status'] ?? '');
                                            $statusClass = 'bg-secondary';
                                            
                                            if ($status == '正常') {
                                                $statusClass = 'bg-success';
                                            } elseif ($status == '异常') {
                                                $statusClass = 'bg-danger';
                                            } elseif ($status == '需复查') {
                                                $statusClass = 'bg-warning text-dark';
                                            }
                                            ?>
                                            <span class="badge <?php echo $statusClass; ?> badge-status">
                                                <?php echo $status ?: '未设置'; ?>
                                            </span>
                                        </td>
                                        <td class="action-buttons">
                                            <a href="view.php?id=<?php echo $record['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="edit.php?id=<?php echo $record['id']; ?>" class="btn btn-sm btn-outline-secondary">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="delete.php?id=<?php echo $record['id']; ?>" class="btn btn-sm btn-outline-danger" 
                                               onclick="return confirm('确定要删除这条记录吗？')">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="bi bi-clipboard-x"></i>
                        </div>
                        <h4>暂无健康记录</h4>
                        <p class="text-muted">该用户目前没有任何健康检查记录</p>
                        <a href="create.php?user_id=<?php echo $user['id']; ?>" class="btn btn-primary btn-action mt-3">
                            <i class="bi bi-plus-circle me-1"></i>添加第一条记录
                        </a>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="tab-pane fade" id="summary" role="tabpanel" aria-labelledby="summary-tab">
                <?php if (count($records) > 0): ?>
                    <div class="health-summary">
                        <div class="summary-item">
                            <div class="summary-icon text-primary">
                                <i class="bi bi-clipboard-check"></i>
                            </div>
                            <div class="summary-value"><?php echo count($records); ?></div>
                            <div class="summary-label">总检查次数</div>
                        </div>
                        
                        <?php
                        // 计算最近一年的检查次数
                        $lastYearCount = 0;
                        $oneYearAgo = date('Y-m-d', strtotime('-1 year'));
                        foreach ($records as $record) {
                            if ($record['exam_date'] >= $oneYearAgo) {
                                $lastYearCount++;
                            }
                        }
                        ?>
                        <div class="summary-item">
                            <div class="summary-icon text-success">
                                <i class="bi bi-calendar-check"></i>
                            </div>
                            <div class="summary-value"><?php echo $lastYearCount; ?></div>
                            <div class="summary-label">最近一年检查次数</div>
                        </div>
                        
                        <?php
                        // 计算异常检查次数
                        $abnormalCount = 0;
                        foreach ($records as $record) {
                            if (isset($record['status']) && $record['status'] == '异常') {
                                $abnormalCount++;
                            }
                        }
                        ?>
                        <div class="summary-item">
                            <div class="summary-icon text-danger">
                                <i class="bi bi-exclamation-triangle"></i>
                            </div>
                            <div class="summary-value"><?php echo $abnormalCount; ?></div>
                            <div class="summary-label">异常检查次数</div>
                        </div>
                        
                        <?php
                        // 计算最近检查日期
                        $latestDate = !empty($records) ? $records[0]['exam_date'] : '无';
                        ?>
                        <div class="summary-item">
                            <div class="summary-icon text-info">
                                <i class="bi bi-calendar-event"></i>
                            </div>
                            <div class="summary-value" style="font-size: 1.2rem;"><?php echo $latestDate; ?></div>
                            <div class="summary-label">最近检查日期</div>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-header">
                            <i class="bi bi-bar-chart-line me-2"></i>检查类型分布
                        </div>
                        <div class="card-body">
                            <?php
                            // 统计检查类型分布
                            $examTypes = [];
                            foreach ($records as $record) {
                                $type = $record['exam_type'] ?? '未分类';
                                if (!isset($examTypes[$type])) {
                                    $examTypes[$type] = 0;
                                }
                                $examTypes[$type]++;
                            }
                            ?>
                            <div class="row">
                                <?php foreach ($examTypes as $type => $count): ?>
                                    <div class="col-md-3 col-sm-6 mb-3">
                                        <div class="p-3 bg-light rounded text-center">
                                            <h5><?php echo htmlspecialchars($type); ?></h5>
                                            <div class="display-6"><?php echo $count; ?></div>
                                            <div class="small text-muted">次检查</div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="bi bi-graph-down"></i>
                        </div>
                        <h4>暂无数据可供分析</h4>
                        <p class="text-muted">需要添加健康记录才能生成健康概览</p>
                        <a href="create.php?user_id=<?php echo $user['id']; ?>" class="btn btn-primary btn-action mt-3">
                            <i class="bi bi-plus-circle me-1"></i>添加健康记录
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<script>
$(document).ready(function() {
    $('#healthRecordsTable').DataTable({
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.11.5/i18n/zh.json'
        },
        order: [[0, 'desc']], // 默认按日期降序排列
        responsive: true,
        columnDefs: [
            { orderable: false, targets: 5 } // 禁用操作列排序
        ]
    });
});
</script>

<?php require_once '../../includes/footer.php'; ?>