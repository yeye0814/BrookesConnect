<?php
class Student {
    private $conn;
    private $table_name = "students";

    public $student_id;
    public $brookes_email;
    public $nickname;
    public $faculty;
    public $academic_level;
    public $major;
    public $year_of_study;
    public $created_at;
    public $is_active;

    public function __construct($db) {
        $this->conn = $db;
    }

    // 获取所有活跃学生
    public function getAllActiveStudents() {
        $query = "SELECT student_id, brookes_email, nickname, faculty, academic_level, major, year_of_study 
                  FROM " . $this->table_name . " 
                  WHERE is_active = TRUE 
                  ORDER BY nickname";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt;
    }

    // 根据ID获取学生信息
    public function getStudentById($student_id) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE student_id = ? AND is_active = TRUE";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $student_id);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // 赋值给对象属性
            $this->student_id = $row['student_id'];
            $this->brookes_email = $row['brookes_email'];
            $this->nickname = $row['nickname'];
            $this->faculty = $row['faculty'];
            $this->academic_level = $row['academic_level'];
            $this->major = $row['major'];
            $this->year_of_study = $row['year_of_study'];
            
            return true;
        }
        
        return false;
    }

    // 获取学生的兴趣标签
    public function getStudentInterests($student_id) {
        $query = "SELECT it.tag_name, it.tag_category, si.interest_level 
                  FROM student_interests si 
                  JOIN interest_tags it ON si.tag_id = it.tag_id 
                  WHERE si.student_id = ? 
                  ORDER BY it.tag_category, si.interest_level DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $student_id);
        $stmt->execute();
        
        return $stmt;
    }

    // 获取学生参加的社团
    public function getStudentSocieties($student_id) {
        $query = "SELECT bs.society_name, bs.society_category, ss.role, ss.joined_date 
                  FROM student_societies ss 
                  JOIN brookes_societies bs ON ss.society_id = bs.society_id 
                  WHERE ss.student_id = ? 
                  ORDER BY bs.society_category, bs.society_name";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $student_id);
        $stmt->execute();
        
        return $stmt;
    }

    // 创建新学生
    public function createStudent() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET brookes_email=:brookes_email, nickname=:nickname, faculty=:faculty, 
                      academic_level=:academic_level, major=:major, year_of_study=:year_of_study";
        
        $stmt = $this->conn->prepare($query);
        
        // 清理数据
        $this->brookes_email = htmlspecialchars(strip_tags($this->brookes_email));
        $this->nickname = htmlspecialchars(strip_tags($this->nickname));
        $this->faculty = htmlspecialchars(strip_tags($this->faculty));
        $this->academic_level = htmlspecialchars(strip_tags($this->academic_level));
        $this->major = htmlspecialchars(strip_tags($this->major));
        
        // 绑定参数
        $stmt->bindParam(":brookes_email", $this->brookes_email);
        $stmt->bindParam(":nickname", $this->nickname);
        $stmt->bindParam(":faculty", $this->faculty);
        $stmt->bindParam(":academic_level", $this->academic_level);
        $stmt->bindParam(":major", $this->major);
        $stmt->bindParam(":year_of_study", $this->year_of_study);
        
        if ($stmt->execute()) {
            $this->student_id = $this->conn->lastInsertId();
            return true;
        }
        
        return false;
    }
}
?>