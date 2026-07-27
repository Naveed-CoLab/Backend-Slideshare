<style type="text/css">.codehap_danger{background:#c03232; color:white; padding:10px; border-radius: 8px;}.grid-container{display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));grid-gap:10px;justify-content:center;align-items:center}.grid-item{border-radius: 14px; border:1px solid #ded8f3; text-align:center; overflow: hidden; background:#fff; box-shadow:0 12px 28px rgba(48,42,86,.10);}.grid-button{ background:#ffaa55; box-shadow:inset 0 0 0 0 transparent;box-sizing:border-box;color:#1f1f1f;cursor:pointer;display:inline-flex;text-decoration:none;outline:0;padding:0 16px;text-align:center;text-rendering:geometricprecision;text-transform:none;user-select:none;-webkit-user-select:none;touch-action:manipulation;vertical-align:middle;width:100%;font-weight:600;font-size:18px;line-height:1.3;min-height:48px;border-radius:8px;justify-content:center;align-items:center;transition:box-shadow .15s ease-in,background-color .15s ease-in}.grid-button:active,.grid-button:hover{background-color:#f99129;background-position:0 0;color:#1f1f1f;box-shadow:inset 0 0 0 1px rgba(0,0,0,.12)}.grid-button:active{opacity:1}
 

.IMGcontainer {
  position: relative;
  text-align: center;
  color: white;
}

.bottom-left {
  position: absolute;
  bottom: 8px;
  left: 16px;
}

.top-left {
  position: absolute;
  top: 5px;
  left: 5px;
}

.top-right {
  position: absolute;
  top: 8px;
  right: 16px;
}

.bottom-right {
  position: absolute;
  bottom: 8px;
  right: 16px;
}

.centered {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
}




.top-left input {
  width: 30px;
  height: 30px;
  accent-color: #003232;
}



 
/* CSS */
.button-1 {
  background-color: #ffaa55;
  border-radius: 8px;
  border-style: none;
  box-sizing: border-box;
  color: #1f1f1f;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-family: Inter, system-ui, Arial, sans-serif;
  font-size: 18px;
  font-weight: 600;
  height: 48px;
  line-height: 1.3;
  list-style: none;
  margin: 10px;
  outline: none;
  padding: 0 16px;
  position: relative;
  text-align: center;
  text-decoration: none;
  transition: box-shadow .15s ease-in,background-color .15s ease-in;
  vertical-align: baseline;
  user-select: none;
  -webkit-user-select: none;
  touch-action: manipulation;
}

.button-1:hover,
.button-1:focus {
  background-color: #f99129;
  color: #1f1f1f;
  box-shadow: inset 0 0 0 1px rgba(0,0,0,.12);
}


.codehap-container{
background: #fff;
border-radius: 18px;
margin: 15px 0px;
padding: 20px 10px;
box-shadow: 0 18px 45px rgba(48,42,86,.12);
}
</style>

<?php error_reporting(0);
include'function.php';

$turnstileToken = isset($_POST['cf-turnstile-response']) ? (string) $_POST['cf-turnstile-response'] : '';
if (!verify_turnstile_token($turnstileToken, isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '')) {
    echo "<div class='codehap_danger'>Captcha verification failed. Please try again.</div>";
    exit;
}

$downloadP = str_replace("result.php", "download.php", $_SERVER['REQUEST_URI']);



if (isset($_POST['codehap_link']) && $_POST['codehap_link'] !== "")
{
    $qu = $_POST['qu'];

    $url = $_POST['codehap_link'];

    if (strpos($url, 'slideshare') !== false)
    {
        $grab = data($url);
        $data = json_decode($grab, true);
        $count = count($data);
?>









<div class="codehap-container">

  <form id="imageForm" method="POST">

    
     <!-- Button 1 to submit to /img.php -->
        <button class="button-1" type="button" id="btn1">Download As Zip</button>
        
        <!-- Button 2 to submit to /pdf.php -->
        <button class="button-1" type="button" id="btn2">Download As PDF</button>


<div class="grid-container">


   
<?php
$img_count = 1;
        foreach ($data as $value)
        {

$imgnum = $img_count++;

            $img = $value['low'];

             
                $qudl = $value[$qu];
            
echo '<div class="grid-item"><div class="IMGcontainer"><label for="img'.$imgnum.'"> <img  src = "' . $img . '" width="100%"></label>
<div class="bottom-left">Bottom Left</div> <div class="top-left"><input id="img'.$imgnum.'" type="checkbox" name="selectedImages[]" value="' . $qudl . '" checked> </div>
</div><a class="grid-button" href="' . $downloadP . '?link=' . $qudl . '" download>Download</a></div>';

        }

echo '</div></form>';

    }

    else
    {
        echo "<div class='codehap_danger'>Something Went Wrong! Please Try Again</div>";
    }
}
else
{
    echo "<div class='codehap_danger'>Something Went Wrong! Please Try Again</div>";
}
?>





</div>



 <script>
    
        // Get references to the form and buttons
        var myForm = document.getElementById('imageForm');
        var btn1 = document.getElementById('btn1');
        var btn2 = document.getElementById('btn2');

        // Add click event listeners to the buttons
        btn1.addEventListener('click', function() {
            myForm.action = '/img.php'; // Change the action for img.php
            myForm.submit(); // Submit the form
        });

        btn2.addEventListener('click', function() {
            myForm.action = '/pdf.php'; // Change the action for pdf.php
            myForm.submit(); // Submit the form
        });
    </script>