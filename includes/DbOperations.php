<?php 

    class DbOperations{

        private $con; 

        function __construct(){
            require_once dirname(__FILE__) . '/DbConnect.php';
            $db = new DbConnect; 
            $this->con = $db->connect(); 
        }

    //Tocreate new user
        public function createUser($email, $password, $name){
           if(!$this->isEmailExist($email)){
                $stmt = $this->con->prepare("INSERT INTO users (email, password, name) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $email, $password, $name);
                if($stmt->execute()){
                    return USER_CREATED; 
                }else{
                    return USER_FAILURE;
                }
           }
           return USER_EXISTS; 
        }
 //To login user
        public function userLogin($email, $password){
            if(!$this->isEmailExist($email)){
                return USER_NOT_FOUND; 
            }else{
                $hashed_password = $this->getUsersPasswordByEmail($email); 
                // $verify=password_verify($password, $hashed_password)
                if($password==$hashed_password){
                    return USER_AUTHENTICATED;
                }else{
                    return USER_PASSWORD_DO_NOT_MATCH; 
                }
                
            }
        }

        private function getUsersPasswordByEmail($email){
            $stmt = $this->con->prepare("SELECT password FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute(); 
            $stmt->bind_result($password);
            $stmt->fetch(); 
            return $password; 
        }

        public function getAllUsers(){
            $stmt = $this->con->prepare("SELECT id, email, name  FROM users;");
            $stmt->execute(); 
            $stmt->bind_result($id, $email, $name);
            $users = array(); 
            while($stmt->fetch()){ 
                $user = array(); 
                $user['id'] = $id; 
                $user['email']=$email; 
                $user['name'] = $name; 
                array_push($users, $user);
            }             
            return $users; 
        }
        public function getAllSubject(){
            $stmt = $this->con->prepare("SELECT id,Sub_title,Sub_Image  FROM subject;");
            $stmt->execute(); 
            $stmt->bind_result($id, $Sub_title, $Sub_Image);
            $Subject = array(); 
            while($stmt->fetch()){ 
                $subject = array(); 
                $subject['id'] = $id;   
                $subject['subImage'] = $Sub_Image; 
                $subject['subtitle']=$Sub_title; 
                array_push($Subject, $subject);
            }             
            return $Subject; 
        }
        public function getAllResult(){
            $stmt = $this->con->prepare("SELECT id,sub_title,unit_no,Chapter_name,Correct_answer ,Question_attempted ,percentage_result  FROM result;");
            $stmt->execute(); 
            $stmt->bind_result($id,$sub_title , $unit_no,$Chapter_name,$Correct_answer,$Question_attempted,$percentage_result);
            $Result = array(); 
            while($stmt->fetch()){ 
                $Results = array(); 
                $Results['id']=$id;
                $Results['sub_title']=$sub_title; 
                $Results['unit_no'] = $unit_no; 
                $Results['Chapter_name'] = $Chapter_name; 
                $Results['Correct_answer'] = $Correct_answer; 
                $Results['Question_attempted'] = $Question_attempted; 
                $Results['percentage_result'] = $percentage_result; 
                array_push($Result, $Results);
            }             
            return $Result; 
        }
        public function getResultbyId($id){
            if($this->isIdExist($id)){
            $stmt = $this->con->prepare("SELECT id,sub_title,unit_no,Chapter_name,Correct_answer ,Question_attempted ,percentage_result,user_id   FROM result where user_id =?;;");
            $stmt->bind_param("s", $id);
            $stmt->execute();
            $stmt->bind_result($id,$sub_title , $unit_no,$Chapter_name,$Correct_answer,$Question_attempted,$percentage_result,$user_id);
            $Result = array(); 
            while($stmt->fetch()){ 
                $Results = array(); 
                $Results['id']=$id;
                $Results['sub_title']=$sub_title; 
                $Results['unit_no'] = $unit_no; 
                $Results['Chapter_name'] = $Chapter_name; 
                $Results['Correct_answer'] = $Correct_answer; 
                $Results['Question_attempted'] = $Question_attempted; 
                $Results['percentage_result'] = $percentage_result; 
                array_push($Result, $Results);
            }             
            return $Result; 
        }}
        public function getChaptersbyId($id){
            if($this->isIdExist($id)){
            $stmt = $this->con->prepare("SELECT `id`, `Chapter_name`, `Chapter_images`, `Subject_id`FROM `chapters` where Subject_id=?");
            $stmt->bind_param("s", $id);
            $stmt->execute();
            $stmt->bind_result($id,$Chapter_name,$Chapter_images,$Subject_id);
            $Chapter = array(); 
            while($stmt->fetch()){ 
                $Chapters = array(); 
                $Chapters['id']=$id;
                $Chapters['Chapter_name'] = $Chapter_name; 
                $Chapters['Chapter_images'] = $Chapter_images; 
                array_push($Chapter, $Chapters);
            }             
            return $Chapter; 
        }}
        public function getUsersbyid($id){
            if($this->isIdExist($id)){
            $stmt = $this->con->prepare("SELECT id,email, name  FROM users where id =?;");
            $stmt->bind_param("s", $id);
            $stmt->execute(); 
            $stmt->bind_result($id,$email, $name);
            $user = array(); 
            while($stmt->fetch()){ 
                $users = array(); 
                $users['id']=$id;
                $users['email']=$email; 
                $users['name'] = $name; 
                array_push($user, $users);
            }             
            return $users; 
         }
        }
        public function getUserByEmail($email){
            $stmt = $this->con->prepare("SELECT id, email, name FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute(); 
            $stmt->bind_result($id, $email, $name);
            $stmt->fetch(); 
            $user = array(); 
            $user['id'] = $id; 
            $user['email']=$email; 
            $user['name'] = $name; 
            return $user; 
        }

        public function updateUser($email, $name,  $id){
            $stmt = $this->con->prepare("UPDATE users SET email = ?, name = ? WHERE id = ?");
            $stmt->bind_param("sssi", $email, $name, $id);
            if($stmt->execute())
                return true; 
            return false; 
        }

        public function updatePassword($currentpassword, $newpassword, $email){
            $hashed_password = $this->getUsersPasswordByEmail($email);
            
            if(password_verify($currentpassword, $hashed_password)){
                
                $hash_password = password_hash($newpassword, PASSWORD_DEFAULT);
                $stmt = $this->con->prepare("UPDATE users SET password = ? WHERE email = ?");
                $stmt->bind_param("ss",$hash_password, $email);

                if($stmt->execute())
                    return PASSWORD_CHANGED;
                return PASSWORD_NOT_CHANGED;

            }else{
                return PASSWORD_DO_NOT_MATCH; 
            }
        }

        public function deleteUser($id){
            $stmt = $this->con->prepare("DELETE FROM users WHERE id = ?");
            $stmt->bind_param("i", $id);
            if($stmt->execute())
                return true; 
            return false; 
        }

        private function isEmailExist($email){
            $stmt = $this->con->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute(); 
            $stmt->store_result(); 
            return $stmt->num_rows > 0;  
        }
        private function isIdExist($id){
            $stmt = $this->con->prepare("SELECT id FROM users WHERE id = ?");
            $stmt->bind_param("s", $id);
            $stmt->execute(); 
            $stmt->store_result(); 
            return $stmt->num_rows > 0;  
        }
    }

    ?>