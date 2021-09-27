<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Europe Countries Factbook</title>
    <!-- force CSS to reload-->
    <link rel="stylesheet" type="text/css" href="countries.css?v=<?php echo time(); ?>">
</head>

<body>
    <div id="main">

        <h1>Europe Countries Factbook</h1>

        <?php

        // ********************************
        // store user inputs into variables
        // ********************************

        // check-population/birth-rate/death-rate inputs (checkboxes)
        $population_checked = isset($_GET['check-population']) && $_GET['check-population'] == 'yes';
        $birth_rate_checked = isset($_GET['check-birth-rate']) && $_GET['check-birth-rate'] == 'yes';
        $death_rate_checked = isset($_GET['check-death-rate']) && $_GET['check-death-rate'] == 'yes';

        // filter-population inputs (no need to check isset since always set)
        $population_filtered = $_GET['filter-population'];
        $population_filtered_value = $_GET['filter-population-value'];

        // filter-birth-death-rate input (no need to check isset since always set)
        $birth_death_rate_filtered = $_GET['filter-birth-death-rate'];

        // filter-language input (no need to check isset since always set)
        $language_filtered = $_GET['filter-language'];


        // **************************
        // user inputs error checking
        // **************************

        // error case 1 - filter-population set to none but a value entered
        if ($population_filtered == 'none' && $population_filtered_value != '') {
            exit('<h2 class="error">Total population error:</h2><p class="error">Please do not set value if you select "None".</p>');
        }

        // error case 2 - filter-population set to greater/less than but value absent
        if ($population_filtered != 'none' && $population_filtered_value == '') {
            exit('<h2 class="error">Total population error:</h2><p class="error">Please set a value if you select "Greater than" or "Less than".</p>');
        }


        // *************
        // load xml file
        // *************

        $file = 'cia.xml';

        if (file_exists($file)) {
            $xml = simplexml_load_file($file);
        } else {
            // exit with error if file not found
            exit('<h2 class="error">File error:</h2><p class="error">Failed to load ' . $file . '.</p>');
        }

        ?>

        <table>
            <tr>
                <th id="col-country-name">Country Name</th>

                <?php

                // **********************************
                // add column headers checked by user 
                // **********************************

                // if population checked
                if ($population_checked) {
                    echo '<th id="col-total-population">Total Population <i>(thousands of people)</i></th>';
                }
                // if birth-rate checked
                if ($birth_rate_checked) {
                    echo '<th id="col-total-birth-rate">Birth Rate <i>(per 1,000)</i></th>';
                }
                // if death-rate checked
                if ($death_rate_checked) {
                    echo '<th id="col-total-death-rate">Death Rate <i>(per 1,000)</i></th>';
                }
                ?>

            </tr>

            <?php

            // ***************************
            // construct xpath expressions
            // *************************** 

            // if filter not needed at all
            if ($population_filtered == 'none' && $birth_death_rate_filtered == 'not specified' && $language_filtered == '') {
                $xpath_country_filtered = '//country/people';
            } else {
                // if at least one filter needed (add square bracket '[')
                $xpath_country_filtered = '//country/people[';

                // concatenate population filter if needed
                if ($population_filtered == 'greater than') {
                    $xpath_country_filtered = $xpath_country_filtered . 'population/total > ' . $_GET['filter-population-value'];
                } else if ($population_filtered == 'less than') {
                    $xpath_country_filtered = $xpath_country_filtered . 'population/total < ' . $_GET['filter-population-value'];
                }

                // concatenate birth/death rate filter if needed
                if ($birth_death_rate_filtered == 'greater than') {
                    // check if and operator needed 
                    if ($population_filtered != 'none') $xpath_country_filtered = $xpath_country_filtered . ' and ';
                    $xpath_country_filtered = $xpath_country_filtered . 'population/birth > population/death';
                } else if ($birth_death_rate_filtered == 'less than') {
                    // check if and operator needed
                    if ($population_filtered != 'none') $xpath_country_filtered = $xpath_country_filtered . ' and ';
                    $xpath_country_filtered = $xpath_country_filtered . 'population/birth < population/death';
                }

                // concatenate language filter if needed
                if ($language_filtered != '') {
                    // check if and operator needed
                    if ($population_filtered != 'none' | $birth_death_rate_filtered != 'not specified') $xpath_country_filtered = $xpath_country_filtered . ' and ';
                    $xpath_country_filtered = $xpath_country_filtered . 'contains(languages, "' . $_GET["filter-language"] . '")';
                }

                // enclose xpath_country_filtered with square bracket ']'
                $xpath_country_filtered = $xpath_country_filtered . ']';
            }

            // attach final parts to the xpaths  
            $xpath_names = $xpath_country_filtered . '/../name';
            $xpath_total_populations = $xpath_country_filtered . '/../people/population/total';
            $xpath_birth_rates = $xpath_country_filtered . '/../people/population/birth';
            $xpath_death_rates = $xpath_country_filtered . '/../people/population/death';


            // *************************************************************
            // retrieve data (to arrays) using constructed xpath expressions
            // *************************************************************

            $names = $xml->xpath($xpath_names);
            $total_populations = $xml->xpath($xpath_total_populations);
            $birth_rates = $xml->xpath($xpath_birth_rates);
            $death_rates = $xml->xpath($xpath_death_rates);


            // *****************
            // add data to table 
            // *****************

            for ($i = 0; $i < sizeof($names); $i++) {
                echo '<tr>';
                echo '<td>' . $names[$i] . '</td>';
                // if population checked
                if ($population_checked) {
                    echo '<td>' . $total_populations[$i] . '</td>';
                }
                // if birth-rate checked
                if ($birth_rate_checked) {
                    echo '<td>' . $birth_rates[$i] . '</td>';
                }
                // if death-rate checked
                if ($death_rate_checked) {
                    echo '<td>' . $death_rates[$i] . '</td>';
                }
                echo '</tr>';
            }

            ?>
        </table>
    </div>
</body>

</html>