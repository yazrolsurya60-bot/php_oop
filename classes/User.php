<?php
class User extends Database
{
    protected $id_users;
    protected $username;
    protected $password;
    protected $nama;
    protected $alamat;
    protected $jenis_kelamin;
    protected $foto_profil;

    public function __construct($id_users, $username, $password, $nama, $alamat, $jenis_kelamin, $foto_profil)
    {
        parent::__construct();
        $this->id_users = $id_users;
        $this->username = $username;
        $this->password = $password;
        $this->nama = $nama;
        $this->alamat = $alamat;
        $this->jenis_kelamin = $jenis_kelamin;
        $this->foto_profil = $foto_profil;
    }

    public function login($username, $password)
    {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && $password === $user['password']) {
            session_start();
            $_SESSION['id_users'] = $user['id_users'];
            // Jika Anda memiliki logic pengambilan role (Admin/Kasir/Koki), bisa ditambahkan di sini
            return true;
        }
        return false;
    }

    public function logout()
    {
        session_start();
        session_unset();
        session_destroy();
        return true;
    }

    public function updateProfile($nama, $alamat, $foto_profil)
    {
        $stmt = $this->conn->prepare("UPDATE users SET nama = ?, alamat = ?, foto_profil = ? WHERE id_users = ?");
        $stmt->bind_param("ssss", $nama, $alamat, $foto_profil, $this->id_users);
        $berhasil = $stmt->execute();

        if ($berhasil) {
            $this->nama = $nama;
            $this->alamat = $alamat;
            $this->foto_profil = $foto_profil;
            return true;
        }
        return false;
    }

    public function gantiPassword($oldPassword, $newPassword)
    {
        $stmt = $this->conn->prepare("SELECT password FROM users WHERE id_users = ?");
        $stmt->bind_param("s", $this->id_users);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        if ($row && $oldPassword === $row['password']) {
            $NewPassword = $newPassword;
            $updateStmt = $this->conn->prepare("UPDATE users SET password = ? WHERE id_users = ?");
            $updateStmt->bind_param("ss", $NewPassword, $this->id_users);

            if ($updateStmt->execute()) {
                $this->password = $NewPassword;
                return true;
            }
        }
        return false;
    }

    public function getNama()
    {
        return $this->nama;
    }
    public function getFotoProfile()
    {
        return $this->foto_profil;
    }

    public function uploadGambar($file)
    {
        $namaFile = $file['name'];
        $error = $file['error'];
        $tmpName = $file['tmp_name'];
        if ($error === 4)
            return "default.png";
        $ekstensiValid = ['jpg', 'jpeg', 'png'];
        $ekstensi = strtolower(pathinfo($namaFile, PATHINFO_EXTENSION));
        if (!in_array($ekstensi, $ekstensiValid))
            return false;
        $namaFileBaru = uniqid() . '.' . $ekstensi;
        move_uploaded_file($tmpName, 'images/' . $namaFileBaru);
        return $namaFileBaru;
    }
}
?>