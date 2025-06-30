<?php
session_start();
$page_password = '123456'; // ======= 自定义密码 =======

if (!isset($_SESSION['filebox_auth'])) {
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['pwd'])) {
        if ($_POST['pwd'] === $page_password) {
            $_SESSION['filebox_auth'] = true;
            header("Location: ".$_SERVER['PHP_SELF']);
            exit;
        } else {
            $msg = '密码错误！';
        }
    }
    // 未登录界面
    echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>登录</title>
    <style>
        body{font-family:sans-serif;background:#fafaff;}
        .login-box{margin:10% auto;width:320px;padding:32px 24px 16px 24px;border-radius:12px;
        box-shadow:0 4px 24px #aaa2;background:#fff;}
        .login-box input[type=password]{width:100%;padding:8px;margin-top:12px;border-radius:6px;border:1px solid #ddd;}
        .login-box button{margin-top:18px;width:100%;padding:10px 0;background:#409eff;color:#fff;border:none;border-radius:8px;}
        .login-box .msg{color:#f33;padding:8px 0;}
    </style>
    </head><body><div class="login-box">
        <h3>请输入访问密码</h3>';
    if (isset($msg)) echo '<div class="msg">'.$msg.'</div>';
    echo '<form method="post">
        <input type="password" name="pwd" placeholder="密码">
        <button type="submit">登录</button>
    </form>
    </div></body></html>';
    exit;
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>文件互传</title>
    <style>
        body { font-family: sans-serif; margin: 2em; }
        .file-list { margin-top: 2em; }
        ul { list-style: none; padding: 0; }
        li { margin: 8px 0; }
        form.inline { display: inline; }
        .del-btn { color: #f33; border: none; background: none; cursor: pointer; font-size: 14px;}
        .logout { position:fixed;top:10px;right:30px;}
    </style>
</head>
<body>
    <a class="logout" href="?logout=1">退出登录</a>
    <h2>上传文件</h2>
    <form method="post" enctype="multipart/form-data">
        <input type="file" name="file[]" multiple>
        <button type="submit">上传</button>
    </form>
    <?php
    // 注销功能
    if (isset($_GET['logout'])) {
        unset($_SESSION['filebox_auth']);
        header("Location: ".$_SERVER['PHP_SELF']);
        exit;
    }
    $dir = __DIR__ . '/uploads';
    if (!is_dir($dir)) mkdir($dir, 0777, true);

    // 文件上传处理
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file'])) {
        foreach ($_FILES['file']['tmp_name'] as $k => $tmp) {
            if (is_uploaded_file($tmp)) {
                $name = basename($_FILES['file']['name'][$k]);
                // 防止同名覆盖
                $dst = "$dir/$name";
                $i = 1;
                while (file_exists($dst)) {
                    $info = pathinfo($name);
                    $name2 = $info['filename']."_{$i}.".$info['extension'];
                    $dst = "$dir/$name2";
                    $i++;
                }
                move_uploaded_file($tmp, $dst);
                echo "<p>上传成功: ".htmlspecialchars($name)."</p>";
            }
        }
    }

    // 文件删除处理
    if (isset($_GET['delete'])) {
        $delname = basename($_GET['delete']);
        $delfile = "$dir/$delname";
        if (is_file($delfile)) {
            unlink($delfile);
            echo "<p style='color:red;'>已删除: ".htmlspecialchars($delname)."</p>";
        }
    }
    ?>
    <div class="file-list">
        <h2>文件列表</h2>
        <ul>
        <?php
        foreach (scandir($dir) as $file) {
            if ($file === '.' || $file === '..') continue;
            $url = "uploads/" . rawurlencode($file);
            echo "<li>
                <a href='$url' download>".htmlspecialchars($file)."</a>
                <form class='inline' method='get' onsubmit='return confirm(\"确定删除 $file ？\");'>
                    <input type='hidden' name='delete' value='".htmlspecialchars($file)."'>
                    <button class='del-btn'>删除</button>
                </form>
            </li>";
        }
        ?>
        </ul>
    </div>
</body>
</html>
