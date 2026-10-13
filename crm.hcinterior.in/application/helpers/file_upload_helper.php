<?php 
function upload_single_file($field_name, $allowed_ext = [])
{
    $target_path = getcwd() . "/uploads/";
    $result = null;

    if (!empty($_FILES[$field_name]['name'])) {

        $original_name = $_FILES[$field_name]['name'];
        $tmp_name      = $_FILES[$field_name]['tmp_name'];
        $ext           = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed_ext)) {
            return null;
        }

        $new_name = time() . '_' . uniqid() . '.' . $ext;

        if (move_uploaded_file($tmp_name, $target_path . $new_name)) {
            $result = [
                'fileName' => $new_name,
                'path'     => 'uploads/' . $new_name,
                'fullPath' => $target_path . $new_name,
                'size'     => round($_FILES[$field_name]['size'] / 1024, 2),
                'is_image' => in_array($ext, ['jpg','jpeg','png']) ? 1 : 0,
                'ext'      => $ext
            ];
        }
    }

    return $result;
}


?>