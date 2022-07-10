<?php

$pdo = new PDO(getenv('DSN'));

$res = $pdo->query('SELECT id, basket, created_at, sold_at FROM bid;');

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Index</title>
    <style>
        table,
        tr,
        td {
            border: 1px;
            border-color: black;
            border-style: solid;
        }
    </style>
</head>

<body>
    <p>
        The price model is simple, the more you buy the less you pay:<br />
        round_up(amount_of_product / 10)
        <= this is how we calculate each section of payment.<br />
        WHERE:<br />
        &emsp;&emsp;amount_of_product = how much of EACH product you buy<br />
        &emsp;&emsp;round_up = function to round up the section to not end up with fractions<br />
        So if you buy 95 boxes of rifle ammo the section calculation will be:<br />
        ((95 - (95 % 10)) + 10) / 10 = 10<br />
        So each section will be at max 10 boxes of ammo
    <ol reversed>
        <li>
            10 boxes for 10% profit
        </li>
        <li>
            10 boxes for 9% profit
        </li>
        <li>
            10 boxes for 8% profit
        </li>
        <li>
            10 boxes for 7% profit
        </li>
        <li>
            10 boxes for 6% profit
        </li>
        <li>
            10 boxes for 5% profit
        </li>
        <li>
            10 boxes for 4% profit
        </li>
        <li>
            10 boxes for 3% profit
        </li>
        <li>
            10 boxes for 2% profit
        </li>
        <li>
            5 boxes for 1% profit
        </li>
    </ol>
    </p>
    <table>
        <thead>
            <tr>
                <td>
                    ID
                </td>
                <td>
                    Basket
                </td>
                <td>
                    Created at
                </td>
                <td>
                    Sold at
                </td>
                <td>
                    Link
                </td>
            </tr>
        </thead>
        </tbody>
        <?php foreach ($res as $row) : ?>
            <tr>
                <td>
                    <?php echo $row['id'] ?>
                </td>
                <td>
                    <?php echo $row['basket'] ?>
                </td>
                <td>
                    <?php echo $row['created_at'] ?>
                </td>
                <td>
                    <?php echo $row['sold_at'] !== null ? $row['sold_at'] : 'Not sold yet' ?>
                </td>
                <td>
                    <a href="/bid.php?uuid=<?php echo $row['id'] ?>">link</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</body>

</html>