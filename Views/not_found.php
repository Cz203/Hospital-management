<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Not Found</title>
    <link rel="stylesheet" href="./style.css">
</head>
<style>
* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

body {
    height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    flex-direction: column;
    font-family: "Montserrat";
    color: rgb(56, 56, 56);
}

.wrapper {
    display: flex;
    align-items: center;
    flex-direction: column;
}

.wrapper h1 {
    font-size: 3rem;
    margin-top: 20px;
}

.wrapper .message {
    font-size: 1.5rem;
    padding: 20px;
    width: 60%;
    text-align: center;
}

.wrapper .btn {
    background: rgb(0, 195, 154);
    padding: 20px;
    font-size: 1.5rem;
    text-decoration: none;
    color: #fff;
}

.wrapper .btn:hover {
    background: rgb(0, 231, 201);
}

.wrapper .copyRights {
    margin-top: 50px;
}
</style>

<body>
    <img src="./assets/img/bg/404.svg" alt="">
    <div class="wrapper">
        <h1>Trang không tồn tại</h1>
        <p class="message">
            Trang bạn đang tìm kiếm không tồn tại hoặc đã bị di chuyển.
            Vui lòng kiểm tra lại đường dẫn hoặc quay về trang chủ.
        </p>
        <a href="./home" class="btn">Về trang chủ</a>
        <p class="copyRights">&copy; ThinhViet Clinic</p>
    </div>
</body>

</html>