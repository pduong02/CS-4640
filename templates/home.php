 <!--CS 4640 Main Login site for project  -->
 <!-- Google Cloud url:  https://storage.googleapis.com/musicmirror/CS-4640/index.html -->
 <!-- Components:  -->
 <!DOCTYPE html>
 <html lang="en">
     <head> <title>MusicMirror</title>
      <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
      <!-- <link rel="stylesheet" href="bootstrap.css"> -->
      <link rel="stylesheet" href="./styles/home_styles.css">
      <link rel="stylesheet/less" type="text/css" href="./styles/styles.less" />
         <meta charset="utf-8">
         <meta http-equiv="X-UA-Compatible" content="IE=edge">
         <meta name="viewport" content="width=device-width, initial-scale=1"> 

         <meta name="author" content="Patrick Duong">
         <meta name="description" content="Patrick Duong and Max Kouzel's CS 4640 semester project: MoodMirror.">
         <meta name="keywords" content="Patrick Duong and Max Kouzel's CS 4640 semester project: MoodMirror, which is an active music library that generates curated production-based recommendations for users. ">     

    </head>

     <body>
      
      <!-- navbar with page title -->

        <!--- Navigation between sites -->
      <nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="?action=home" style="font-weight: bolder">MusicMirror.</a>
            <button class="navbar-toggler collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#navbarsExample05" aria-controls="navbarsExample05" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse me-auto" id="navbarsExample05" style="float:right;">
                <div class="ms-auto">
                    <ul class="navbar-nav">
                        <li class="nav-item"><a href="?action=home" class="nav-link active">Home</a></li>
                        <li class="nav-item"><a href="?action=library" class="nav-link">Library</a></li>
                        <li class="nav-item"><a href="?action=reflection" class="nav-link">Reflection</a></li>
                        <li class="nav-item"><a href="?action=logout" class="nav-link">Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
      </nav>
                

                <!-- MusicMirror main login hero/welcome screen  -->
      <section class = "container-fluid col-12" style = "padding-top: 60px" >
        <div class="bg-dark text-secondary px-4 py-3 text-center">
            <div class="py-3">
              <h1 class="display-5 fw-bold text-white " style="font-size:4vw;">Welcome Back, <strong class = "gradient-text" style = "font-style: italic;"><?=$user['name']?>.</strong></h1>
                  <div class="col-lg-6 mx-auto">
                    <p class="fs-5 mb-4" style = "color: grey">Based on your recent adds, we've curated these three songs as your daily recommendations:</p>
          
                  </div>
                </div>
        </div>
      </section>

      <!-- Song recommendations as thumbnails with supporting text, using bootstrap template-->
      <div class="album py-5 bg-light">
        <div class="container" id="recs">
          <div class="row g-3" id='loading'>
            <p class='fs-5 mb-4'>Generating your recommendations, please wait...</p>
          </div>

          <!-- ---------------------------------------------------------- -->
          <!-- song recommendations  
          <?php 
            if (!empty($error_msg)) {
              echo "<div class='alert alert-danger col-4'>$error_msg</div>";
            }
          
            if (empty($recommendations)) {
              echo '<div class="row g-3">';
              echo "<p class='fs-5 mb-4'>All your recommendations for today have been added to your library. Check back again tomorrow for your new recommendations!</p>";
              echo '</div>';
            } else if (isset($recommendations)) {
              echo '<div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-3">';
              foreach ($recommendations as $rec) {
                $json = json_encode($rec);
                echo "<div class=\"col\">";
                echo "<div class=\"card shadow-sm\">";
                echo "<img class=\"card-img-top\" src=\"{$rec['image_url']}\" alt=\"Recommended song album cover\">";

                echo "<div class=\"card-body mx-fixed\">";
                echo "<h5 class=\"card-title song-title\">{$rec['title']}</h5>";
                echo "<h6 class=\"card-subtitle mb-2 text-muted\">{$rec['primary_artist']}</h6>";
                echo "<small class=\"card-text\" style = \"padding-bottom: 10px\" >Because you liked a song produced by {$rec['producer']}</small>";
                echo "<div class=\"d-flex justify-content-between align-items-center\">";
                echo "<div class=\"btn-group\">";
                echo "<a href='{$rec['genius_url']}' class=\"btn btn-sm btn-outline-secondary\" target='_blank'>Listen</a>";
                echo "<form action='?action=home' method='post'>";
                echo "<button type=\"submit\" class=\"btn btn-sm btn-outline-secondary\">Add to Library</button>";
                echo "<input type='hidden' name='songinfo' value='{$json}'>";
                echo "</form>";
                echo "</div>";
                echo "</div>";
                echo "</div>";
                echo "</div>";
                echo "</div>";
              }
              echo "</div>";
            } else {
              echo "Recommendations not set";
            }
          ?>
          ----------------------------------------------------------- -->
        </div>
      </div>

      <footer class = "primary-footer row">

          <small class = "copyright">&#169; Patrick Duong and Max Kouzel.</small>

      </footer>

      <!-- JavaScript CDNs -->
      <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
      <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
      
      <script>
        $.get("?action=getRecommendations", (resp) => {
          resp = JSON.parse(resp);
          $('#loading').hide();


          if (typeof(resp) == 'string') {
            $('#recs').append("<div class='alert alert-danger col-4'>"+resp+"</div>");
          } else if (resp.length == 0) {
            $('#recs').append('<div class="row g-3">');
            $('#recs div').append("<p class='fs-5 mb-4'>All your recommendations for today have been added to your library. Check back again tomorrow for your new recommendations!</p>");
          } else {
            
            // add the recommendations to the HTML page
            $('#recs').append('<div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-3" id="recsrow">');
            // at most 3 recommendations
            for (var i = 0; i < 3; i++) {
              if (i in resp) {
                $('#recsrow').append("<div class=\"col\" id='reccol"+i+"'>");
                $('#reccol'+i).append("<div class=\"card shadow-sm\" id='reccard"+i+"'>");
                $('#reccard'+i).append("<img class=\"card-img-top\" src=\""+resp[i]['image_url']+"\" alt=\"Recommended song album cover\">");
                $('#reccard'+i).append("<div class=\"card-body mx-fixed\" id='cardbody"+i+"'>");
                $('#cardbody'+i).append("<h5 class=\"card-title song-title\">"+resp[i]['title']+"</h5>");
                $('#cardbody'+i).append("<h6 class=\"card-subtitle mb-2 text-muted\">"+resp[i]['primary_artist']+"</h6>");
                $('#cardbody'+i).append("<small class=\"card-text\" style = \"padding-bottom: 10px\" >Because you liked a song produced by "+resp[i]['producer']+"</small>");
                $('#cardbody'+i).append("<div class=\"d-flex justify-content-between align-items-center\" id='recbtns"+i+"'>");
                $('#recbtns'+i).append("<div class=\"btn-group\" id='btngroup"+i+"'>");
                $('#btngroup'+i).append("<a href='"+resp[i]['genius_url']+"' class=\"btn btn-sm btn-outline-secondary\" target='_blank'>Listen</a>");
                $('#btngroup'+i).append("<form action='?action=home' method='post' id='recform"+i+"'>");
                $('#recform'+i).append("<button type=\"submit\" class=\"btn btn-sm btn-outline-secondary\">Add to Library</button>");
                $('#recform'+i).append("<input type='hidden' name='songinfo' value='"+JSON.stringify(resp[i])+"'>");
                }
            }
          }
        });
      </script>
    </body>
 </html>
