<?php
session_start();

$PYTHON_PATH = "\"C:\\Users\\Franky Halim\\AppData\\Local\\Microsoft\\WindowsApps\\python.exe\"";
$PYTHON_FILE = './simple_predictions_website.py';
$GRAB_SCRIPT = './get_news_article.py';
$MODEL = './models/bert-base-multilingual-uncased_sigmoid_none_use_token_type_ids_1_sent_rep_tokens_5e-05_0.1_8_4_transformer_2_fixed.ckpt'; # Used model
$PARSER_MODEL = '--model';                // Trained model path
$PARSER_SOURCE = '--source';              // News source
$PARSER_PERCENTAGES = '--percentages';    // Percentages of summarized news article
$PARSER_SAVE_DIR = '--save_dir';
$UPLOAD_FILE_DIR = './kumpulan_berita/';  // Directory in which the uploaded file will be moved


$dest_path = '';
$message = ''; 
$file_content = '';
$execute_result='';
$flag = 0;
$percentages = 0.0;


$start = microtime(true);
if (isset($_POST['summarizeBtn']) && $_POST['summarizeBtn'] === 'Summarize' && isset($_POST['inputPercentages']))
{

  if (isset($_FILES['uploadedNewsFile']['name']) && $_FILES['uploadedNewsFile']['error'] === UPLOAD_ERR_OK)
  {
    // Get details of the uploaded file (tmp_name, name, size, type)
    $fileTmpPath = $_FILES['uploadedNewsFile']['tmp_name'];
    $fileName = $_FILES['uploadedNewsFile']['name'];

    $dest_path = $UPLOAD_FILE_DIR . $fileName;

    if(move_uploaded_file($fileTmpPath, $dest_path)) 
    {
      $file_content = file_get_contents($dest_path, FILE_USE_INCLUDE_PATH);
      $percentages = $_POST['inputPercentages'];
      $message ='File is successfully uploaded.';
      $_SESSION['original_news'] = $file_content;
    }
    else 
    {
    	$message = 'Upload failed';
    }
  }
  else if (isset($_POST['news_url'])){
    $dest_path = $_POST['news_url'];
    $percentages = $_POST['inputPercentages'];
    $message = 'News article successfully grabbed';
    $grab = $PYTHON_PATH.' '.$GRAB_SCRIPT.' '.$PARSER_SOURCE.' '.$dest_path;
    $_SESSION['original_news'] = shell_exec($grab);
  }
  else
  {
    $message = 'There is some error while uploading file or grab news article text.';
  }
  $flag = 1;
}

// Execute python file (prediction)
$command = $PYTHON_PATH.' '.$PYTHON_FILE.' '.$PARSER_MODEL.' '.$MODEL.' '.$PARSER_SOURCE.' '.$dest_path.' '.$PARSER_PERCENTAGES.' '.$percentages;
$execute_result = shell_exec($command);
$res = json_decode($execute_result, true);

$end = microtime(true);
$elapsed_time = round($end - $start,3); //Elapsed time in seconds
$message .= '\nElapsed time: '.$elapsed_time.' seconds.';

$_SESSION['result'] = $res;
$_SESSION['percentages'] = $percentages;
$_SESSION['flag'] = $flag;
$_SESSION['message'] = $message;

header("Location: index.php");

?>