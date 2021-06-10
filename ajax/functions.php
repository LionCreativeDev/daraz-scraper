<?php

error_reporting(0);

// Report simple running errors
error_reporting(E_ERROR | E_WARNING);

header("Access-Control-Allow-Origin: *");

//if($_SERVER['HTTP_REFERER'] !== 'https://domain.ccom/' && $_SERVER['HTTP_REFERER'] !== 'https://www.domain.com/'){
//    //die('Unauthorized access');
//    die('');
//}

$useragents = [];
array_push($useragents,"Mozilla/5.0 (Windows NT 6.3; Win64; x64; rv:75.0) Gecko/20100101 Firefox/75.0");//firefox (Windows)
array_push($useragents,"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/81.0.4044.129 Safari/537.36");//Chrome (Windows)
array_push($useragents,"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_4) AppleWebKit/600.7.12 (KHTML, like Gecko) Version/8.0.7 Safari/600.7.12");//Chrome (Mac)
array_push($useragents,"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_2) AppleWebKit/601.3.9 (KHTML, like Gecko) Version/9.0.2 Safari/601.3.9");//Chrome (Mac)
array_push($useragents,"Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:15.0) Gecko/20100101 Firefox/15.0.1");//Firefox (Linux)
array_push($useragents,"Mozilla/5.0 (X11; CrOS x86_64 8172.45.0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.64 Safari/537.36");//Chrome (Chrome-OS)
array_push($useragents,"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/42.0.2311.135 Safari/537.36 Edge/12.246");//Edge (windows)
array_push($useragents,"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/81.0.4044.129 Safari/537.36");//Safari (Mac)

$useragents;
$randIndex = array_rand($useragents);
$ua = $useragents[$randIndex];

function ScrapeProducts($input){
    //$useragent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.77 Safari/537.36';
    global $ua;
    $timeout = 5;

    $c = curl_init('https://www.daraz.pk/catalog/?q='.$input.'&from=suggest_normal&sugg='.$input);
    curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($c, CURLOPT_USERAGENT, $ua);
    curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($c, CURLOPT_SSL_VERIFYHOST,false);
    curl_setopt($c, CURLOPT_SSL_VERIFYPEER,false);
    curl_setopt($c, CURLOPT_MAXREDIRS, 10);
    curl_setopt($c, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($c, CURLOPT_CONNECTTIMEOUT, $timeout);

    $html = curl_exec($c);

    if (curl_error($c))
        die(curl_error($c));

    // Get the status code
    $status = curl_getinfo($c, CURLINFO_HTTP_CODE);

    curl_close($c);

    //echo $html;
    /*$myfile = fopen("daraz".$input.".txt", "w") or die("Unable to open file!");
    fwrite($myfile, $html);
    fclose($myfile);*/
    
    parse_result($html);
}

function ScrapeProductslocal()
{
    $html = file_get_contents("../daraz.pk.txt");
    parse_result($html);
}

function parse_result($html)
{
    try{
        $start = "<script>window.pageData="; // replace ... with exact other text you are maching
        $end = "</script>";
        $startsAt = strpos($html, $start) + strlen($start);
        $endsAt = strpos($html, $end, $startsAt);
        $json_string = substr($html, $startsAt, $endsAt - $startsAt);

        $array = json_decode($json_string, true);
        
        $no = 1;
        $products = array();
        if(isset($array["mods"]["listItems"])){
            foreach ($array["mods"]["listItems"] as $value)
            {
                if($no <= 30)
                    array_push($products, array("no"=>$no, "itemId"=>$value["itemId"], "image"=>$value["image"], "name"=>$value["name"], "priceShow"=>$value["priceShow"], "ratingScore"=>$value["ratingScore"], "review"=>$value["review"], "location"=>$value["location"], "brandName"=>$value["brandName"], "sellerName"=>$value["sellerName"], "inStock"=>($value["inStock"] ? 'true':'false'), "productUrl"=>$value["productUrl"]));
                $no++;
            }
            echo '{"status":"success","products":'.json_encode($products).'}';
        }
        else{
            echo json_encode(array("status"=>"failure", "message"=>"Sorry! Unable to find products details"));
        }
    }
    catch(Exception $e) {
        echo json_encode(array("status"=>"failure", "message"=>"Sorry! Unable to find products details"));
    }
}

function ScrapeProductDetails($productUrl)
{
    //$useragent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.77 Safari/537.36';
    global $ua;
    $timeout = 5;

    $c = curl_init($productUrl);
    curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($c, CURLOPT_USERAGENT, $ua);
    curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($c, CURLOPT_SSL_VERIFYHOST,false);
    curl_setopt($c, CURLOPT_SSL_VERIFYPEER,false);
    curl_setopt($c, CURLOPT_MAXREDIRS, 10);
    curl_setopt($c, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($c, CURLOPT_CONNECTTIMEOUT, $timeout);

    $html = curl_exec($c);

    if (curl_error($c))
        die(curl_error($c));

    // Get the status code
    $status = curl_getinfo($c, CURLINFO_HTTP_CODE);

    curl_close($c);
    
    parseproduct_result($html);
}

function ScrapeProductDetailslocal()
{
    $html = file_get_contents("../darazproduct.txt");
    parseproduct_result($html);
}

function parseproduct_result($html)
{
    try {
        $start = "app.run({"; // replace ... with exact other text you are maching
        $end = "});";
        $startsAt = strpos($html, $start) + strlen($start);
        $endsAt = strpos($html, $end, $startsAt);
        $json_string = '{'.substr($html, $startsAt, $endsAt - $startsAt).'}';

        $array = json_decode($json_string, true);
        
        if(isset($array["data"]["root"]["fields"]["skuInfos"][0]["stock"])){
            $stock = $array["data"]["root"]["fields"]["skuInfos"][0]["stock"];
            echo json_encode(array("status"=>"success", "stock"=> $stock));
        }
        else{
            echo json_encode(array("status"=>"failure", "message"=>"Sorry! Unable to find stock details"));
        }
    }
    catch(Exception $e) {
        echo json_encode(array("status"=>"failure", "message"=>"Sorry! Unable to find stock details"));
    }
}

if(isset($_GET["action"]) && $_GET["action"] === "scrape_product" && isset($_GET["searchterm"]) && $_GET["searchterm"] !== "")
{
	//ScrapeProductslocal();
    $input = str_replace(' ', '+', trim($_GET["searchterm"]));
    ScrapeProducts($input);
}
elseif(isset($_GET["action"]) && $_GET["action"] === "scrape_stock" && isset($_GET["product_url"]) && $_GET["product_url"] !== "")
{
	//ScrapeProductDetailslocal();
    ScrapeProductDetails($_GET["product_url"]);
}

?>