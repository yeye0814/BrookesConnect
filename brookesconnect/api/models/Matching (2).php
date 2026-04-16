<?php
class Matching {
    private $conn;
    private $table_name = "matches";

    public $match_id;
    public $student1_id;
    public $student2_id;
    public $match_score;
    public $match_type;
    public $match_date;
    public $is_active;

    public function __construct($db) {
        $this->conn = $db;
    }

    // 获取匹配推荐（基于数据库视图）
    public function getMatchRecommendations($student_id, $limit = 10) {
        $query = "SELECT * FROM match_recommendations 
                  WHERE student1_id = ? OR student2_id = ? 
                  ORDER BY match_score DESC 
                  LIMIT ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $student_id);
        $stmt->bindParam(2, $student_id);
        $stmt->bindParam(3, $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt;
    }

    // 创建新的匹配记录
    public function createMatch() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET student1_id=:student1_id, student2_id=:student2_id, 
                      match_score=:match_score, match_type=:match_type";
        
        $stmt = $this->conn->prepare($query);
        
        // 绑定参数
        $stmt->bindParam(":student1_id", $this->student1_id);
        $stmt->bindParam(":student2_id", $this->student2_id);
        $stmt->bindParam(":match_score", $this->match_score);
        $stmt->bindParam(":match_type", $this->match_type);
        
        return $stmt->execute();
    }

    // 获取平台分析数据
    public function getPlatformAnalytics() {
        $query = "SELECT * FROM platform_analytics";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt;
    }

    // 基于兴趣计算匹配分数
    public function calculateInterestMatch($student1_id, $student2_id) {
        $query = "SELECT 
                    COUNT(DISTINCT si1.tag_id) as shared_interests,
                    COUNT(DISTINCT ss1.society_id) as shared_societies,
                    ROUND(
                        (COUNT(DISTINCT si1.tag_id) * 0.6 + 
                         COUNT(DISTINCT ss1.society_id) * 0.4) / 
                        GREATEST(COUNT(DISTINCT si1.tag_id) + COUNT(DISTINCT ss1.society_id), 1), 4
                    ) as match_score
                  FROM students s1
                  JOIN students s2 
                  LEFT JOIN student_interests si1 ON s1.student_id = si1.student_id
                  LEFT JOIN student_interests si2 ON s2.student_id = si2.student_id AND si1.tag_id = si2.tag_id
                  LEFT JOIN student_societies ss1 ON s1.student_id = ss1.student_id
                  LEFT JOIN student_societies ss2 ON s2.student_id = ss2.student_id AND ss1.society_id = ss2.society_id
                  WHERE s1.student_id = ? AND s2.student_id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $student1_id);
        $stmt->bindParam(2, $student2_id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>