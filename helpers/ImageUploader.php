<?php

class ImageUploader
{
    private $uploadDir = './uploads/';
    private $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
    private $maxSize = 5 * 1024 * 1024; // 5MB

    public function __construct($uploadDir = null)
    {
        if ($uploadDir) {
            $this->uploadDir = $uploadDir;
        }

        // Tạo thư mục uploads nếu chưa tồn tại
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }

    public function uploadImage($file, $prefix = 'doctor_')
    {
        try {
            // Kiểm tra file có tồn tại không
            if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
                throw new Exception('Không có file được upload hoặc có lỗi xảy ra');
            }

            // Kiểm tra kích thước file
            if ($file['size'] > $this->maxSize) {
                throw new Exception('File quá lớn. Kích thước tối đa là 5MB');
            }

            // Lấy thông tin file
            $fileName = $file['name'];
            $fileTmpName = $file['tmp_name'];
            $fileSize = $file['size'];
            $fileError = $file['error'];

            // Lấy extension
            $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            // Kiểm tra loại file
            if (!in_array($fileExt, $this->allowedTypes)) {
                throw new Exception('Loại file không được hỗ trợ. Chỉ chấp nhận: ' . implode(', ', $this->allowedTypes));
            }

            // Tạo tên file mới
            $newFileName = $prefix . uniqid() . '.' . $fileExt;
            $uploadPath = $this->uploadDir . $newFileName;

            // Upload file
            if (move_uploaded_file($fileTmpName, $uploadPath)) {
                return $newFileName;
            } else {
                throw new Exception('Không thể upload file');
            }
        } catch (Exception $e) {
            throw new Exception('Lỗi upload: ' . $e->getMessage());
        }
    }

    public function deleteImage($fileName)
    {
        $filePath = $this->uploadDir . $fileName;
        if (file_exists($filePath)) {
            return unlink($filePath);
        }
        return false;
    }

    public function resizeImage($sourcePath, $targetPath, $width = 300, $height = 300)
    {
        // Kiểm tra GD extension
        if (!extension_loaded('gd')) {
            return false;
        }

        // Lấy thông tin ảnh
        $imageInfo = getimagesize($sourcePath);
        if (!$imageInfo) {
            return false;
        }

        $originalWidth = $imageInfo[0];
        $originalHeight = $imageInfo[1];
        $mimeType = $imageInfo['mime'];

        // Tạo image resource
        switch ($mimeType) {
            case 'image/jpeg':
                $sourceImage = imagecreatefromjpeg($sourcePath);
                break;
            case 'image/png':
                $sourceImage = imagecreatefrompng($sourcePath);
                break;
            case 'image/gif':
                $sourceImage = imagecreatefromgif($sourcePath);
                break;
            default:
                return false;
        }

        // Tạo ảnh mới
        $targetImage = imagecreatetruecolor($width, $height);

        // Giữ transparency cho PNG
        if ($mimeType === 'image/png') {
            imagealphablending($targetImage, false);
            imagesavealpha($targetImage, true);
        }

        // Resize
        imagecopyresampled($targetImage, $sourceImage, 0, 0, 0, 0, $width, $height, $originalWidth, $originalHeight);

        // Lưu ảnh
        $result = false;
        switch ($mimeType) {
            case 'image/jpeg':
                $result = imagejpeg($targetImage, $targetPath, 90);
                break;
            case 'image/png':
                $result = imagepng($targetImage, $targetPath, 9);
                break;
            case 'image/gif':
                $result = imagegif($targetImage, $targetPath);
                break;
        }

        // Giải phóng memory
        imagedestroy($sourceImage);
        imagedestroy($targetImage);

        return $result;
    }

    public function validateImage($file)
    {
        $errors = [];

        // Kiểm tra file có tồn tại không
        if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Không có file được upload hoặc có lỗi xảy ra';
            return $errors;
        }

        // Kiểm tra kích thước
        if ($file['size'] > $this->maxSize) {
            $errors[] = 'File quá lớn. Kích thước tối đa là 5MB';
        }

        // Kiểm tra loại file
        $fileName = $file['name'];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (!in_array($fileExt, $this->allowedTypes)) {
            $errors[] = 'Loại file không được hỗ trợ. Chỉ chấp nhận: ' . implode(', ', $this->allowedTypes);
        }

        // Kiểm tra MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        $allowedMimes = [
            'image/jpeg',
            'image/jpg',
            'image/png',
            'image/gif'
        ];

        if (!in_array($mimeType, $allowedMimes)) {
            $errors[] = 'Loại file không hợp lệ';
        }

        return $errors;
    }
}
