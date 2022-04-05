<?php

class MMController {
    private $db;
    private $action;

    public function __construct($action) {
        $this->db = new DataBase();
        $this->action = $action;
    }

    public function run() {
        switch ($this->action) {
            case "library":
                $this->showLibrary();
                break;
            case "addsong":
                $this->addSong();
                break;
            case "reflection":
                $this->getReflection();
                break;
            case "home":
                $this->home();
                break;
            case "logout":
                $this->destroySession();
            case "login":
                // check email with regex
                // store user name/email in cookie
            default:
                $this->login();                
        }
    }

    private function destroySession() {
        session_unset();
        session_destroy();
        session_start();
    }

    public function login() {
        if (isset($_POST["email"]) && !empty($_POST["email"]) && !empty($_POST["name"])) {
            $data = $this->db->query("select * from users where email = ?;", "s", $_POST["email"]);
            if ($data === false) {
                $error_msg = "Error checking for user";
            } else if (!empty($data)) {
                // returning user
                if (password_verify($_POST["password"], $data[0]["password"])) {
                    $_SESSION["name"] = $data[0]["name"];
                    $_SESSION["email"] = $data[0]["email"];
                    $_SESSION['userid'] = $data[0]["userid"];
                    header("Location: ?action=home");
                } else {
                    $error_msg = "Wrong password";
                }
            } else {
                // new user
                $insert = $this->db->query("insert into users (name, email, password) values (?, ?, ?);", 
                                                                "sss", $_POST["name"], $_POST["email"], password_hash($_POST["password"], PASSWORD_DEFAULT));
            $data = $this->db->query("select * from users where email = ?;", "s", $_POST["email"]);

                if ($insert === false) {
                    $error_msg = "Error inserting user";
                } else {
                    $_SESSION["name"] = $_POST["name"];
                    $_SESSION["email"] = $_POST["email"];
                    $_SESSION['userid'] = $data[0]["userid"];
                    header("Location: ?action=home");
                }
            }
        }

        include("templates/login.php");
    }
    

    public function showLibrary() {
        $user = [
            "name" => $_SESSION["name"],
            "email" => $_SESSION["email"],
            "id" => $_SESSION["userid"]
        ];

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $hits = $this->searchGenius($_POST["form-songtitle"], $_POST["form-artist"]);
            
            if ($hits === false) {
                $error_msg = "Failed to make genius request.";
            }
            
            $_SESSION["hits"] = $hits['response']['hits'];
            $_SESSION["title"] = $_POST["form-songtitle"];
            $_SESSION["artist"] = $_POST["form-artist"];

            header("Location: ?action=addsong", true, 303);
        } else if ($_SERVER["REQUEST_METHOD"] == "GET") {
            $songs = $this->db->query("select * from songs where userid = ?", "i", $user['id']);

            if ($songs===false) {
                $error_msg = "Failed to select songs from database";
            }
        }

        include('templates/library.php');
    }

    public function addSong() {
        $user = [
            "name" => $_SESSION["name"],
            "email" => $_SESSION["email"],
            "id" => $_SESSION['userid']
        ];

        echo print_r($user);

        if (!isset($_SESSION["hits"])) {
            $error_msg = "Hits not stored properly.";
            header("Location: ?action=library");
        } else if ($_SERVER["REQUEST_METHOD"] == "GET") {
            $hits = $_SESSION["hits"];
            $title = $_SESSION["title"];
            $artist = $_SESSION["artist"];

            include("templates/addsong.php");
        } else { // POST request
            if (isset($_POST['songinfo'])) {
                $songinfo = json_decode($_POST['songinfo'], true);
                $res = $this->db->query("insert into songs (userid, title, primary_artist, geniusid, image_url) values (?, ?, ?, ?, ?)", "issis", $user['id'], 
                                        $songinfo['title'], $songinfo['artist'], $songinfo['songid'], $songinfo['image_url']);
                if ($res === false) {
                    $error_msg = "Failed to insert into songs table";
                }

                header("Location: ?action=library");
                
            } else {
                $error_msg = "Did not successfully encode song info";
                echo $error_msg;
            }
        }
    }

    private function searchGenius($title, $artist) {
        $search_term = str_replace(" ", "%20", $title . " " . $artist);

        $client_access_token = Config::$access_token;
        $genius_search_url = "http://api.genius.com/search?q={$search_term}&access_token={$client_access_token}";

        $curl = curl_init($genius_search_url);
        curl_setopt($curl, CURLOPT_URL, $genius_search_url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        
        $resp = curl_exec($curl);
        curl_close($curl);
        return json_decode($resp, true);
    }

    //return artists ranked by # of appearances: 
    public function artistRanking(){
        $userid = $_SESSION['userid'];
        $artists = $this->db->query("select primary_artist, count(*) as count FROM songs where userid = $userid group by primary_artist");

        usort($artists, function ($artist1, $artist2) {
            return $artist2['count'] <=> $artist1['count'];
        });

        // echo print_r($artists);
        return $sortedArtists = $artists;
    }

    //return songs grouped by age: 
    public function ageGrouping($user_songs){
        $years = array();
        $twenty20s = 0;
        $twenty10s = 0;
        $twenty0s= 0;
        $nineteen90s = 0;
        $nineteen80s= 0;
        $oldies = 0;

        foreach ($user_songs as $started){
            $year = explode("-",$started)[0];
            if (intval($year) >= 2020){
                $twenty20s ++;
            }
            else if (intval($year) >= 2010){
                $twenty10s ++;
            }
            else if (intval($year) >= 2000){
                $twenty0s ++;
            }
            else if (intval($year) >= 1990){
                $nineteen90s ++;
            }
            else if (intval($year) >= 1980){
                $nineteen80s ++;
            }
            else if (intval($year) < 1980){
                $oldies++;
            }

        }

        $years["2020s"] = $twenty20s;
        $years["2010s"] = $twenty10s;
        $years["2000s"] = $twenty0s;
        $years["1990s"] = $nineteen90s;
        $years["1980s"] = $nineteen80s;
        $years["oldies"] = $oldies;

        return $years;
    }

  


   function getGeniusSong($songid) {
        $client_access_token = Config::$access_token;
        $genius_search_url = "http://api.genius.com/songs/{$songid}";

        // echo $genius_search_url;

        $curl = curl_init($genius_search_url);
        curl_setopt($curl, CURLOPT_URL, $genius_search_url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);

        $headers = array(
            "Authorization: Bearer ".Config::$access_token,
        );

        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        $resp = curl_exec($curl);
        curl_close($curl);
        return json_decode($resp, true);
}

    //return producer info and launch year info (we can modify to give us more)
    function userSongs(){
        $userid = $_SESSION['userid'];
        $songs = $this->db->query("select * FROM songs where userid = $userid");
        $songs_info = array(); 
        foreach($songs as $song){
            $result = $this->getGeniusSong($song["geniusid"])['response']['song'];
            $songs_info[$result["producer_artists"][0]['name']] = $result["release_date"];
        }

        // echo print_r($songs_info);
        return $songs_info;

    }


    public function getReflection() {
        $data_val = 30;
        $js_out_dval = json_encode($data_val);
        $userSongs = $this->userSongs();
    
        #return top artists: 
        $sortedArtists = $this->artistRanking();

        $top_artist = $sortedArtists[0]["primary_artist"];
        $top_count = $sortedArtists[0]["count"];
    
        #return song age groupings: 
        $years = $this->ageGrouping($userSongs);
        
        #randomly select a producer in the rotation: 
        $producer = array_rand($userSongs,1);
    
        include('templates/reflection.php');
       }
    
       public function home() {
    
        
        include('templates/home.php');
       }

}