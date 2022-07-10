<?php

$bid = $_GET['uuid'];

$valid = preg_match('/^[0-9A-F]{8}-[0-9A-F]{4}-4[0-9A-F]{3}-[89AB][0-9A-F]{3}-[0-9A-F]{12}$/i', $bid);

if (!$valid) {
    echo 'Invalid bid ID, <a href="/">Click here to go back.</a>';
    die;
}

$pdo = new PDO(getenv('DSN'));

$smt = $pdo->prepare('SELECT buyer_phone, basket, tax, created_at, sold_at FROM bid WHERE id=?');

$smt->execute([$bid]);

if ($smt->rowCount() !== 1) {
    echo 'Error';
    die;
}

$row = $smt->fetch();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $bid; ?></title>
    <script>
        function calculate() {
            fetch('/crafting_data.json').then(response => response.json()).then(json => {
                function get_ingredients(ingredients) {
                    const ret = {};

                    for (const [key, ingredient] of Object.entries(ingredients)) {
                        if ('Ingredients' in json[key]) {
                            const temp = get_ingredients(json[key].Ingredients);

                            for (const [key2, value] of Object.entries(temp)) {
                                if (key2 in ret) {
                                    ret[key2] += value * ingredient;
                                } else {
                                    ret[key2] = value * ingredient;
                                }
                            }
                        } else {
                            if (key in ret) {
                                ret[key] += ingredients[key];
                            } else {
                                ret[key] = ingredients[key];
                            }
                        }
                    }

                    return ret;
                }


                let items = {};
                let ingredients = {};

                const bid = JSON.parse(atob("<?php echo $row['basket'] ?>"));

                for (const key of Object.keys(bid)) {
                    items[json[key].Name] = {
                        ingredients: get_ingredients(json[key].Ingredients),
                        total: bid[key]
                    };
                }

                for (const key in items) {
                    const temp = {};

                    for (const ingredient_key in items[key].ingredients) {
                        if (json[ingredient_key].Name in ingredients) {
                            temp[ingredient_key] += items[key].ingredients[ingredient_key] * items[key].total;
                        } else {
                            temp[ingredient_key] = items[key].ingredients[ingredient_key] * items[key].total;
                        }
                    }

                    for (const ingredient_key in temp) {
                        if (ingredient_key in ingredients) {
                            ingredients[ingredient_key] += temp[ingredient_key];
                        } else {
                            ingredients[ingredient_key] = temp[ingredient_key];
                        }
                    }
                }

                const items_element = document.getElementById('items');

                items_element.innerHTML = '';

                for (const [key, value] of Object.entries(items)) {
                    const li = document.createElement('li');

                    li.innerText = value.total + ' units of "' + key + '".';

                    items_element.appendChild(li);
                }

                const ingredients_per_element = document.getElementById('ingredients_per');

                ingredients_per_element.innerHTML = '';

                for (const [key, value] of Object.entries(items)) {
                    const li = document.createElement('li');

                    let text = 'For "' + key + '" I need to buy:';

                    let start = true;

                    for (const [key2, value2] of Object.entries(value.ingredients)) {
                        if (start === true) {
                            text += ' ';
                            start = false;
                        } else {
                            text += ', ';
                        }

                        text += value2 + ' units of "' + json[key2].Name + '"';
                    }

                    li.innerText = text;

                    ingredients_per_element.appendChild(li);
                }

                const ingredients_total_element = document.getElementById('ingredients_total');

                ingredients_total_element.innerHTML = '';

                for (const [key, value] of Object.entries(ingredients)) {
                    const li = document.createElement('li');

                    li.innerText = value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',') + ' units of "' + json[key].Name + '".';

                    ingredients_total_element.appendChild(li);
                }

                const price_per_element = document.getElementById('price_per');
                const price_per_total_element = document.getElementById('price_per_total');

                price_per_element.innerHTML = '';
                price_per_total_element.innerHTML = '';

                for (const [key, value] of Object.entries(items)) {
                    const li = document.createElement('li');
                    const li2 = document.createElement('li');
                    const span = document.createElement('span');
                    const span2 = document.createElement('span');

                    let start = true;

                    span.innerHTML = 'For "' + key + '" the calculation is: ';
                    span2.innerHTML = 'For "' + key + '" the calculation is: ';

                    let price = 0;

                    for (const [key2, value2] of Object.entries(value.ingredients)) {
                        let text = '';

                        const span3 = document.createElement('span');
                        const span4 = document.createElement('span');

                        if (start === true) {
                            text += '(( ';
                            start = false;
                        } else {
                            text += ' + ( ';
                        }

                        span3.title = json[key2].Name;
                        span4.title = json[key2].Name;

                        text += value2 + ' * ' + json[key2].Cost + ' * ' + (100 + <?php echo $row['tax'] ?>) + '% )';

                        const price_per_one = (json[key2].Cost * (100 + <?php echo $row['tax'] ?>)) / 100;

                        price += value2 * price_per_one;

                        span3.innerHTML = text;
                        span4.innerHTML = text;

                        span.appendChild(span3);
                        span2.appendChild(span4);
                    }

                    value.cost = price;

                    span.innerHTML += ' = $' + price.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',')
                    span2.innerHTML += ') * ' + value.total + ' = $' + (price * value.total).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',')

                    li.appendChild(span);

                    li2.appendChild(span2);

                    price_per_total_element.append(li2);

                    price_per_element.appendChild(li);
                }

                const price_total_element = document.getElementById('price_total');

                let total_price = 0;

                price_total_element.innerText = 'Total price to craft: ';

                const last = Object.keys(items)[Object.keys(items).length - 1];

                for (const [key, value] of Object.entries(items)) {
                    const span = document.createElement('span');

                    let start = true;

                    for (const [key2, value2] of Object.entries(value.ingredients)) {
                        let text = '';

                        const span2 = document.createElement('span');

                        if (start === true) {
                            text += '((( ';
                            start = false;
                        } else {
                            text += ' + ( ';
                        }

                        span2.title = json[key2].Name;

                        text += value2 + ' * ' + json[key2].Cost + ' * ' + (100 + <?php echo $row['tax'] ?>) + '% )';

                        const price_per_one = (json[key2].Cost * (100 + <?php echo $row['tax'] ?>)) / 100;

                        total_price += value2 * price_per_one * value.total;

                        span2.innerText = text;

                        span.appendChild(span2);
                    }

                    if (key === last) {
                        span.innerHTML += ' * ' + value.total + ')';
                    } else {
                        span.innerHTML += ' * ' + value.total + ') + ';
                    }

                    price_total_element.appendChild(span);
                }

                price_total_element.innerHTML += ' = $' + total_price.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');

                const sections_element = document.getElementById('sections');

                sections_element.innerHTML = '';

                for (const [key, value] of Object.entries(items)) {
                    const ol = document.createElement('ol');

                    ol.innerHTML = 'For "' + key + '": ';

                    ol.reversed = true;

                    const section_size = Math.floor(value.total / 10);
                    const remains = value.total % 10;

                    value.total_price = 0;

                    for (let i = 10; i > 0; i -= 1) {
                        const li = document.createElement('li');

                        if (i === 10) {
                            li.innerHTML = section_size + remains + ' * ' + value.cost + ' * ' + (100 + i) + '% = $' + (((section_size + remains) * value.cost * (100 + i)) / 100).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',')
                            value.total_price += ((section_size + remains) * value.cost * (100 + i)) / 100;
                        } else {
                            li.innerHTML = section_size + ' * ' + value.cost + ' * ' + (100 + i) + '% = $' + ((section_size * value.cost * (100 + i)) / 100).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',')
                            value.total_price += (section_size * value.cost * (100 + i)) / 100;
                        }

                        ol.appendChild(li);
                    }

                    sections_element.appendChild(ol);

                    sections_element.innerHTML += 'Total buying price: $' + value.total_price.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
                }

                const price_to_buy_element = document.getElementById('price_to_buy');
                
                let finel_price = 0;

                for (const [key, value] of Object.entries(items)) {
                    finel_price += value.total_price
                }

                price_to_buy_element.innerHTML = 'Total buying price: $' + finel_price.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            });
        }

        document.addEventListener('DOMContentLoaded', (event) => {
            calculate();
        });
    </script>
</head>

<body>
    <a href="/">Go back</a><br /><br />
    Basket: <?php echo $row['basket'] ?><br />
    Created at: <?php echo $row['created_at'] ?><br />
    <?php echo $row['buyer_phone'] !== null ? 'Buyer phone: ' . $row['buyer_phone'] . '<br />' : '' ?>
    <?php echo $row['sold_at'] !== null ? 'Sold at: ' . $row['sold_at'] : 'Not sold yet' ?><br /><br />
    <div>
        Items to craft:
        <ol id="items">
        </ol>
    </div>
    <div>
        Ingredients per item:
        <ol id="ingredients_per">
        </ol>
    </div>
    <div>
        Total ingredients:
        <ol id="ingredients_total">
        </ol>
    </div>
    <div>
        Price per item:
        <ol id="price_per">
        </ol>
    </div>
    <div>
        Price per item total:
        <ol id="price_per_total">
        </ol>
    </div>
    <div id="price_total">
    </div><br />
    <div>
        Sections:
        <ol id="sections">
        </ol>
    </div>
    <div id="price_to_buy">
    </div>
</body>

</html>