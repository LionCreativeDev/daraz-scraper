<html lang="en">
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

        <link rel="stylesheet" href="stylesheet/bootstrap.min.css" integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">
        <link rel="stylesheet" href="stylesheet/bootstrap.css">
        <link rel="stylesheet" href="stylesheet/dataTables.bootstrap4.min.css">
        <link rel="stylesheet" href="stylesheet/buttons.dataTables.min.css">

        <style>
            /* width */
            ::-webkit-scrollbar {
                width: 6px;
            }

            /* Track */
            ::-webkit-scrollbar-track {
                background: #f1f1f1; 
            }
            
            /* Handle */
            ::-webkit-scrollbar-thumb {
                background: #888; 
            }

            /* Handle on hover */
            ::-webkit-scrollbar-thumb:hover {
                background: #555; 
            }

            .preloader {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background-color: #fff;
                z-index: 9999;
                display: flex;
                align-items: center;
                justify-content: center;
            }
        </style>
    </head>
    <body style="background: #efecec;">
        <div class="preloader" style="display: none;">
            <img src="img/loader.gif" alt="preloader">
        </div>
        <div class="container" style="padding: 10px 10px 10px 10px;background: #fff;margin-top: 10px;margin-bottom: 10px;">
            <h4>Daraz Scrapper</h4>
            <hr>
            <form class="form-row">

                <div class="form-group col-md-10">
                    <label for="searchterm">Product Name: </label>
                    <input type="text" class="form-control" placeholder="ie: dinner" id="searchterm">
                </div>

                <div class="form-group col-md-2">
                    <br>
                    <button type="submit" class="btn btn-primary callScraper" name="callScraper" style="margin-top: 7px; float:right;" value="true">Scrape Product Details</button>
                </div>

            </form>            
        </div>

        <div class="container rc" style="padding: 10px 10px 10px 10px;background: #fff;margin-top: 20px;margin-bottom: 10px; display:none;">
            <div class="table-responsive">
                <h4>Daraz Scrapped Results</h4>
                
                <table class="table" style="font-size:12px;">
                    <thead>
                        <th>No</td>
                        <th>Item ID</th>
                        <th class="notexport">Main Image</th>
                        <th>Name</th>
                        <th>Price</th>
                        <th>Rating Score</th>
                        <th>Reviews</th>
                        <th>Location</th>
                        <th>Brand Name</th>
                        <th>Saller Name</th>
                        <th>In Stock</th>
                        <th>Stock Quntity</th>
                        <th>Product Url</th>
                    </thead>
                    <tbody>
                    <!--<tr><td>itemId</td><td>name</td><td>priceShow</td><td>ratingScore</td><td>review</td><td>location</td><td>brandName</td><td>sellerName</td><td>inStock</td><td>productUrl</td></tr>-->
                    </tbody>
                </table>
            </div>
        </div>

        <!--<script src="javascript/jquery-3.5.1.slim.min.js"></script>-->
        <script src="javascript/jquery-3.1.1.min.js"></script>
        <script src="javascript/popper.min.js"></script>
        <script src="javascript/bootstrap.min.js"></script>
    
        <script type="text/javascript" src="javascript/jquery.dataTables.min.js"></script>
        <script type="text/javascript" src="javascript/dataTables.bootstrap4.min.js"></script>

        <script type="text/javascript" src="javascript/dataTables.buttons.min.js"></script>
        <script type="text/javascript" src="javascript/buttons.flash.min.js"></script>
        <script type="text/javascript" src="javascript/jszip.min.js"></script>
        <script type="text/javascript" src="javascript/pdfmake.min.js"></script>
        <script type="text/javascript" src="javascript/vfs_fonts.js"></script>
        <script type="text/javascript" src="javascript/buttons.html5.min.js"></script>
        <script type="text/javascript" src="javascript/buttons.print.min.js"></script>

        <script type="text/javascript" language="javascript">

            var interval;
            function bildDataTable(){
                $('.table').DataTable({
                "lengthMenu": [ [1000, 5000, 10000, 50000, -1], [1000, 5000, 10000, 50000, "All"] ],
                dom: 'Bfrtip',
                buttons: [
                    'pageLength',
                    'copy',
                    {
                        extend: 'csvHtml5',
                        title: 'Daraz.pk Scraped Results',
                        exportOptions: {
                            columns: ':not(.notexport)'
                        }
                    },
                    {
                        extend: 'excelHtml5',
                        title: 'Daraz.pk Scraped Results',
                        exportOptions: {
                            columns: ':not(.notexport)'
                        }
                    },
                    {
                        extend: 'pdfHtml5',
                        title: 'Daraz.pk Scraped Results',
                        exportOptions: {
                            columns: ':not(.notexport)'
                        }
                    },
                    'print'
                ]
                });

                $(".dt-button").css({"background":"#007bff","border-color":"#007bff", "color":"white","padding":"2px 10px"});
            }

            function getStockDetails(){
                var row = $("tbody .not").first();
                var url = row.find(".pdUrl").text();

                if (typeof url !== "undefined" && url !== "") {
                    $.ajax({
                        url: "ajax/functions.php"+"?action=scrape_stock&product_url="+url, 
                        async: true,
                        success: function(response) {
                            //$(".table tbody").html(response);
                            var result = JSON.parse(response);
                            if(result["status"] === "success")
                            {
                                row.find(".stockholder").html(result["stock"]);
                                row.removeClass("not");
                                row.find(".imgloader").hide();
                            }
                        }
                    });
                    //console.log('url: ',url);
                }
                else{                    
                    clearTimeout(interval);
                    bildDataTable();
                }
            }

            $(document).ready(function(){

                $(".callScraper").click(function(e){
                    e.preventDefault();
                    if($("#searchterm").val() !== "")
                    {
                        $(".preloader").show();
                        $('.table').DataTable().clear().destroy();
                        $('.table tbody tr').remove();
                        $(".rc").hide();
                        clearTimeout(interval);
                        //alert("calling scraper");
                        $.ajax({
                            url: "ajax/functions.php"+"?action=scrape_product&searchterm="+$("#searchterm").val(), 
                            async: true,
                            success: function(response) {
                                //$(".table tbody").html(response);
                                var result = JSON.parse(response);
                                if(result["status"] === "success")
                                {
                                    for(var i = 0; i < result["products"].length; i++) {
                                        $('.table tbody').append('<tr class="not"><td>'+result["products"][i]["no"]+'</td><td>'+result["products"][i]["itemId"]+'</td><td><img src="'+result["products"][i]["image"]+'" width="100" alt="'+result["products"][i]["name"]+'"/></td><td>'+result["products"][i]["name"]+'</td><td>'+result["products"][i]["priceShow"]+'</td><td>'+result["products"][i]["ratingScore"]+'</td><td>'+result["products"][i]["review"]+'</td><td>'+result["products"][i]["location"]+'</td><td>'+result["products"][i]["brandName"]+'</td><td>'+result["products"][i]["sellerName"]+'</td><td>'+(result["products"][i]["inStock"] ? 'true':'false')+'</td><td class="stockholder"><img class="imgloader" src="img/loader.gif" alt="loader" style="min-width: 100px; max-width: 34px; margin-left: -30px;"></td><td class="pdUrl">https:'+result["products"][i]["productUrl"]+'</td></tr>');
                                        //console.log(result["products"][i]['no']);
                                    }

                                    //bildDataTable();
                                    $(".rc").show();
                                    interval = setInterval(getStockDetails, 2000);
                                    $(".preloader").hide();
                                }
                            }
                        });
                    }
                })            
            });    
        </script>
    </body>
</html>