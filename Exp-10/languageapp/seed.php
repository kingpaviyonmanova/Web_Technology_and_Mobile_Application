<?php
require_once 'db.php';

// Seed 3 levels per language with vocabulary + quiz questions
$pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
$pdo->exec("TRUNCATE TABLE questions");
$pdo->exec("TRUNCATE TABLE levels");
$pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

$langMap = [];
$lr = $pdo->query("SELECT id, name FROM languages");
while ($r = $lr->fetch(PDO::FETCH_ASSOC)) $langMap[$r['name']] = $r['id'];

// [level_number, title, [[word,meaning,example],...], [[question,o1,o2,o3,o4,correct],...]]
$seed = [
'Tamil' => [
[1,'Basic Greetings',[
['Vanakkam (வணக்கம்)','Hello','Vanakkam! Eppadi irukkireerkal?'],
['Nandri (நன்றி)','Thank you','Unga udhavikku nandri!'],
['Aamam (ஆமாம்)','Yes','Aamam, naan purinthukkonden.'],
['Illai (இல்லை)','No','Illai, enakku vendaam.'],
['Vanakkam Sollunga (வணக்கம் சொல்லுங்க)','Say Hello','Periyavargalukku vanakkam sollunga.']
],[
['What does "Vanakkam" mean?','Hello','Sorry','Water','Goodbye',1],
['How do you say "Thank you" in Tamil?','Vanakkam','Nandri','Aamam','Illai',2],
['Which Tamil word means "Yes"?','Illai','Nandri','Aamam','Vanakkam',3],
['"Illai" translates to?','No','Yes','Hello','Thanks',1],
['Which is a greeting in Tamil?','Nandri','Illai','Aamam','Vanakkam',4]
]],
[2,'Numbers & Colors',[
['Onru (ஒன்று)','One','Onru mudhal pathhu varai ennu.'],
['Irandu (இரண்டு)','Two','Irandu kai thatti.'],
['Moondru (மூன்று)','Three','Moondru naal kazhithu vaa.'],
['Sivappu (சிவப்பு)','Red','Roja poo sivappu niram.'],
['Pachchai (பச்சை)','Green','Ilaikal pachchai niram.']
],[
['What does "Onru" mean?','One','Two','Three','Four',1],
['"Irandu" means?','One','Two','Three','Five',2],
['What is "Moondru" in English?','One','Two','Three','Four',3],
['Which Tamil word means "Red"?','Sivappu','Pachchai','Onru','Nandri',1],
['"Pachchai" refers to which color?','Red','Blue','Yellow','Green',4]
]],
[3,'Food & Daily Life',[
['Saadham (சாதம்)','Rice','Enakku saadham venduum.'],
['Thanni (தண்ணீர்)','Water','Oru glass thanni kodungal.'],
['Paal (பால்)','Milk','Kaapi ku paal venduum.'],
['Pazham (பழம்)','Fruit','Ithhu oru nalla pazham.'],
['Kaapi (காபி)','Coffee','Kaalaiyil kaapi kudippen.']
],[
['What does "Saadham" mean?','Rice','Water','Milk','Coffee',1],
['"Thanni" translates to?','Rice','Water','Fruit','Milk',2],
['Which word means "Milk" in Tamil?','Saadham','Thanni','Paal','Kaapi',3],
['What is "Pazham"?','Rice','Water','Coffee','Fruit',4],
['"Kaapi" means?','Tea','Coffee','Milk','Juice',2]
]]
],
'English' => [
[1,'Basic Greetings',[
['Hello','A common greeting','Hello, how are you today?'],
['Thank you','Expressing gratitude','Thank you for helping me.'],
['Please','Polite request word','Please pass the salt.'],
['Goodbye','Farewell greeting','Goodbye, see you tomorrow!'],
['Sorry','Expressing apology','Sorry, I am late.']
],[
['What does "Hello" express?','A greeting','An insult','A farewell','A question',1],
['Which word expresses gratitude?','Sorry','Thank you','Please','Hello',2],
['Which word makes a request polite?','Sorry','Hello','Please','Goodbye',3],
['"Goodbye" is used when?','Arriving','Asking','Eating','Leaving',4],
['Which word is an apology?','Sorry','Hello','Please','Thanks',1]
]],
[2,'Common Phrases',[
['Excuse me','Getting attention politely','Excuse me, where is the station?'],
['How much?','Asking about price','How much does this cost?'],
['I understand','Confirming comprehension','I understand the lesson now.'],
['Help me','Requesting assistance','Can you help me please?'],
['Of course','Agreeing willingly','Of course, I will join you.']
],[
['"Excuse me" is used to?','Get attention politely','Say goodbye','Order food','Sleep',1],
['Which phrase asks about price?','Excuse me','How much?','Help me','Of course',2],
['What does "I understand" confirm?','Hunger','Anger','Comprehension','Boredom',3],
['"Help me" is a request for?','Food','Assistance','Money','Time',2],
['Which phrase shows agreement?','Excuse me','Help me','How much','Of course',4]
]],
[3,'Travel & Directions',[
['Airport','Place for flights','The airport is 10km away.'],
['Hotel','Place to stay','We booked a hotel room.'],
['Turn left','Direction instruction','Turn left at the signal.'],
['Straight ahead','Continue forward','Go straight ahead for 2 blocks.'],
['Restaurant','Place to eat','The restaurant serves great food.']
],[
['Where do you catch flights?','Hotel','Airport','Restaurant','Park',2],
['Where do travelers stay overnight?','Airport','Hospital','Hotel','School',3],
['"Turn left" is a?','Greeting','Direction','Food item','Color',2],
['What does "straight ahead" mean?','Go back','Turn right','Continue forward','Stop',3],
['A restaurant is for?','Sleeping','Eating','Flying','Swimming',2]
]]
],
'Japanese' => [
[1,'Basic Greetings',[
['Konnichiwa (こんにちは)','Hello / Good afternoon','Konnichiwa! Genki desu ka?'],
['Arigatou (ありがとう)','Thank you','Arigatou gozaimasu!'],
['Hai (はい)','Yes','Hai, wakarimashita.'],
['Iie (いいえ)','No','Iie, chigaimasu.'],
['Sayounara (さようなら)','Goodbye','Sayounara, mata ashita!']
],[
['What does "Konnichiwa" mean?','Goodbye','Hello','Sorry','Thanks',2],
['"Arigatou" expresses?','Anger','Gratitude','Sadness','Fear',2],
['Which word means "Yes" in Japanese?','Iie','Sayounara','Hai','Arigatou',3],
['"Iie" translates to?','No','Yes','Maybe','Hello',1],
['How do you say goodbye?','Hai','Arigatou','Konnichiwa','Sayounara',4]
]],
[2,'Numbers & Counting',[
['Ichi (一)','One','Ichi-ban me desu.'],
['Ni (二)','Two','Ni-ko kudasai.'],
['San (三)','Three','San-ji ni aimashō.'],
['Shi / Yon (四)','Four','Yon-nin imasu.'],
['Go (五)','Five','Go-fun matte kudasai.']
],[
['What is "Ichi"?','Two','Three','One','Five',3],
['"Ni" means?','One','Two','Four','Five',2],
['How do you say "Three"?','Go','Ichi','Ni','San',4],
['"Yon" represents which number?','One','Two','Three','Four',4],
['What does "Go" mean?','Three','Four','Five','Six',3]
]],
[3,'Food & Dining',[
['Sushi (寿司)','Vinegared rice dish','Sushi wa oishii desu.'],
['Ramen (ラーメン)','Noodle soup','Ramen o tabemashō.'],
['Ocha (お茶)','Tea','Ocha o onegaishimasu.'],
['Mizu (水)','Water','Mizu o kudasai.'],
['Itadakimasu (いただきます)','Bon appétit / I humbly receive','Itadakimasu! Let us eat.']
],[
['What is "Sushi"?','Soup','Vinegared rice dish','Tea','Bread',2],
['"Ramen" is a type of?','Dessert','Salad','Noodle soup','Drink',3],
['What does "Ocha" mean?','Coffee','Tea','Juice','Milk',2],
['"Mizu" translates to?','Water','Fire','Ice','Rice',1],
['When do you say "Itadakimasu"?','Leaving','Sleeping','Before eating','After eating',3]
]]
],
'Mandarin' => [
[1,'Basic Greetings',[
['Nǐ hǎo (你好)','Hello','Nǐ hǎo! Nǐ hǎo ma?'],
['Xièxie (谢谢)','Thank you','Xièxie nǐ de bāngzhù!'],
['Shì (是)','Yes','Shì de, wǒ míngbái.'],
['Bù (不)','No / Not','Bù, wǒ bù yào.'],
['Zàijiàn (再见)','Goodbye','Zàijiàn, míngtiān jiàn!']
],[
['What does "Nǐ hǎo" mean?','Sorry','Goodbye','Hello','Thanks',3],
['"Xièxie" expresses?','Gratitude','Anger','Fear','Hunger',1],
['Which word means "Yes"?','Bù','Zàijiàn','Xièxie','Shì',4],
['"Bù" translates to?','Yes','No','Hello','Please',2],
['How do you say goodbye in Mandarin?','Nǐ hǎo','Xièxie','Shì','Zàijiàn',4]
]],
[2,'Numbers & Counting',[
['Yī (一)','One','Wǒ yào yī gè.'],
['Èr (二)','Two','Èr shí kuài qián.'],
['Sān (三)','Three','Sān diǎn jiàn.'],
['Sì (四)','Four','Sì gè rén.'],
['Wǔ (五)','Five','Wǔ fēn zhōng.']
],[
['What is "Yī"?','One','Two','Three','Four',1],
['"Èr" means?','One','Two','Three','Five',2],
['How do you say "Three"?','Wǔ','Yī','Sān','Sì',3],
['What number is "Sì"?','One','Three','Five','Four',4],
['"Wǔ" represents?','Three','Four','Five','Six',3]
]],
[3,'Food & Drinks',[
['Mǐfàn (米饭)','Rice','Wǒ yào chī mǐfàn.'],
['Shuǐ (水)','Water','Qǐng gěi wǒ shuǐ.'],
['Chá (茶)','Tea','Wǒ xǐhuān hē chá.'],
['Miàntiáo (面条)','Noodles','Miàntiáo hěn hǎo chī.'],
['Chī fàn (吃饭)','To eat / Have a meal','Wǒmen qù chī fàn ba!']
],[
['"Mǐfàn" means?','Rice','Tea','Noodles','Water',1],
['What is "Shuǐ"?','Tea','Water','Milk','Juice',2],
['"Chá" translates to?','Coffee','Milk','Tea','Soda',3],
['What does "Miàntiáo" mean?','Rice','Bread','Soup','Noodles',4],
['"Chī fàn" means?','Drink water','Cook food','Eat a meal','Buy food',3]
]]
],
'German' => [
[1,'Basic Greetings',[
['Hallo','Hello','Hallo! Wie geht es Ihnen?'],
['Danke','Thank you','Danke für Ihre Hilfe!'],
['Ja','Yes','Ja, ich verstehe.'],
['Nein','No','Nein, das stimmt nicht.'],
['Tschüss','Goodbye (informal)','Tschüss, bis morgen!']
],[
['What does "Hallo" mean?','Goodbye','Hello','Sorry','Thanks',2],
['"Danke" expresses?','Anger','Gratitude','Sadness','Fear',2],
['Which word means "Yes"?','Nein','Tschüss','Ja','Danke',3],
['"Nein" translates to?','Yes','No','Maybe','Please',2],
['How do you say bye informally?','Hallo','Danke','Ja','Tschüss',4]
]],
[2,'Numbers & Colors',[
['Eins','One','Ich habe eins.'],
['Zwei','Two','Zwei Kaffee, bitte.'],
['Drei','Three','In drei Tagen.'],
['Rot','Red','Die Rose ist rot.'],
['Blau','Blue','Der Himmel ist blau.']
],[
['What is "Eins"?','Two','Three','One','Four',3],
['"Zwei" means?','One','Two','Three','Five',2],
['How do you say "Three"?','Eins','Zwei','Drei','Vier',3],
['What color is "Rot"?','Blue','Green','Yellow','Red',4],
['"Blau" means?','Red','Blue','Green','White',2]
]],
[3,'Food & Dining',[
['Brot','Bread','Ich möchte Brot kaufen.'],
['Wasser','Water','Ein Glas Wasser bitte.'],
['Kaffee','Coffee','Ich trinke gern Kaffee.'],
['Kuchen','Cake','Der Kuchen ist lecker.'],
['Bier','Beer','Ein Bier bitte!']
],[
['"Brot" means?','Cake','Bread','Beer','Water',2],
['What is "Wasser"?','Water','Coffee','Beer','Cake',1],
['"Kaffee" translates to?','Tea','Milk','Coffee','Juice',3],
['What does "Kuchen" mean?','Bread','Cheese','Cookie','Cake',4],
['"Bier" means?','Wine','Beer','Juice','Water',2]
]]
],
'French' => [
[1,'Basic Greetings',[
['Bonjour','Hello / Good day','Bonjour! Comment allez-vous?'],
['Merci','Thank you','Merci beaucoup!'],
['Oui','Yes','Oui, je comprends.'],
['Non','No','Non, merci.'],
['Au revoir','Goodbye','Au revoir, à demain!']
],[
['What does "Bonjour" mean?','Goodbye','Night','Hello','Sorry',3],
['"Merci" expresses?','Anger','Gratitude','Fear','Boredom',2],
['Which word means "Yes"?','Non','Merci','Oui','Bonjour',3],
['"Non" translates to?','Yes','No','Maybe','Hello',2],
['How do you say goodbye?','Bonjour','Merci','Oui','Au revoir',4]
]],
[2,'Numbers & Colors',[
['Un','One','J\'ai un chat.'],
['Deux','Two','Deux billets, s\'il vous plaît.'],
['Trois','Three','Dans trois jours.'],
['Rouge','Red','La pomme est rouge.'],
['Bleu','Blue','Le ciel est bleu.']
],[
['What is "Un"?','Two','One','Three','Four',2],
['"Deux" means?','One','Two','Three','Five',2],
['How do you say "Three"?','Un','Deux','Trois','Quatre',3],
['What color is "Rouge"?','Blue','Green','Red','White',3],
['"Bleu" means?','Red','Green','Blue','Yellow',3]
]],
[3,'Food & Dining',[
['Pain','Bread','Je veux du pain.'],
['Eau','Water','Un verre d\'eau, s\'il vous plaît.'],
['Fromage','Cheese','Le fromage est délicieux.'],
['Vin','Wine','Un verre de vin rouge.'],
['Croissant','Croissant pastry','Je prends un croissant.']
],[
['"Pain" means?','Wine','Bread','Cheese','Cake',2],
['What is "Eau"?','Wine','Milk','Water','Tea',3],
['"Fromage" translates to?','Bread','Cheese','Ham','Butter',2],
['What does "Vin" mean?','Water','Beer','Wine','Juice',3],
['A "Croissant" is a type of?','Soup','Drink','Fruit','Pastry',4]
]]
],
'Telugu' => [
[1,'Basic Greetings',[
['Namaskaram (నమస్కారం)','Hello / Greetings','Namaskaram! Ela unnaru?'],
['Dhanyavaadalu (ధన్యవాదాలు)','Thank you','Mee sahayaaniki dhanyavaadalu!'],
['Avunu (అవును)','Yes','Avunu, naaku ardhamainadi.'],
['Kaadu (కాదు)','No','Kaadu, naaku vaddu.'],
['Veltanu (వెళ్తాను)','I will go / Goodbye','Nenu veltanu, taruvata kalustanu.']
],[
['What does "Namaskaram" mean?','Sorry','Hello','Water','Food',2],
['"Dhanyavaadalu" expresses?','Anger','Gratitude','Hunger','Fear',2],
['Which word means "Yes"?','Kaadu','Veltanu','Avunu','Namaskaram',3],
['"Kaadu" translates to?','Yes','No','Hello','Bye',2],
['How do you say goodbye?','Avunu','Namaskaram','Dhanyavaadalu','Veltanu',4]
]],
[2,'Numbers & Colors',[
['Okati (ఒకటి)','One','Naaku okati kavali.'],
['Rendu (రెండు)','Two','Rendu pustakalu ivvu.'],
['Moodu (మూడు)','Three','Moodu rojullo vastanu.'],
['Erra (ఎర్ర)','Red','Idi erra rangu gulaabi.'],
['Pasupu (పసుపు)','Yellow','Pasupu rangu baga untundi.']
],[
['What is "Okati"?','Two','Three','One','Four',3],
['"Rendu" means?','One','Two','Three','Five',2],
['"Moodu" translates to?','One','Two','Three','Four',3],
['What color is "Erra"?','Blue','Red','Green','White',2],
['"Pasupu" means?','Red','Green','Blue','Yellow',4]
]],
[3,'Food & Daily Life',[
['Annam (అన్నం)','Rice','Naaku annam kavali.'],
['Neellu (నీళ్ళు)','Water','Oka glass neellu ivvu.'],
['Paalu (పాలు)','Milk','Tea ki paalu kavali.'],
['Pandu (పండు)','Fruit','Idi manchi pandu.'],
['Koodi (కూడి)','Curry / Side dish','Annam tho koodi tinali.']
],[
['"Annam" means?','Water','Bread','Rice','Milk',3],
['What is "Neellu"?','Milk','Water','Tea','Coffee',2],
['"Paalu" translates to?','Water','Juice','Milk','Rice',3],
['What does "Pandu" mean?','Vegetable','Bread','Rice','Fruit',4],
['"Koodi" refers to?','Dessert','Curry','Drink','Snack',2]
]]
],
'Korean' => [
[1,'Basic Greetings',[
['Annyeonghaseyo (안녕하세요)','Hello','Annyeonghaseyo! Jal jinaeseyo?'],
['Gamsahamnida (감사합니다)','Thank you','Gamsahamnida, jeongmal gomawoyo!'],
['Ne (네)','Yes','Ne, algesseumnida.'],
['Aniyo (아니요)','No','Aniyo, gwaenchansseumnida.'],
['Annyeonghi gaseyo (안녕히 가세요)','Goodbye','Annyeonghi gaseyo! Tto mannayo!']
],[
['What does "Annyeonghaseyo" mean?','Sorry','Hello','Goodbye','Thanks',2],
['"Gamsahamnida" expresses?','Anger','Sadness','Gratitude','Fear',3],
['Which word means "Yes"?','Aniyo','Ne','Gamsahamnida','Annyeonghaseyo',2],
['"Aniyo" translates to?','Yes','Maybe','Hello','No',4],
['How do you say goodbye in Korean?','Ne','Gamsahamnida','Annyeonghi gaseyo','Aniyo',3]
]],
[2,'Numbers & Counting',[
['Hana (하나)','One','Hana, dul, set!'],
['Dul (둘)','Two','Dul da joayo.'],
['Set (셋)','Three','Set si e mannayo.'],
['Net (넷)','Four','Net myeong isseoyo.'],
['Daseot (다섯)','Five','Daseot bun gidariseyo.']
],[
['What is "Hana"?','Two','Three','Four','One',4],
['"Dul" means?','One','Two','Three','Four',2],
['How do you say "Three"?','Hana','Dul','Set','Net',3],
['"Net" represents?','Two','Three','Four','Five',3],
['What does "Daseot" mean?','Three','Four','Five','Six',3]
]],
[3,'Food & Dining',[
['Bap (밥)','Rice / Meal','Bap meogeoyo!'],
['Mul (물)','Water','Mul juseyo.'],
['Kimchi (김치)','Fermented vegetables','Kimchi joa haseyo?'],
['Gogi (고기)','Meat','Gogi gui meokgo sipeoyo.'],
['Jal meokgesseumnida (잘 먹겠습니다)','Bon appétit','Jal meokgesseumnida! Let us eat!']
],[
['"Bap" means?','Water','Rice','Meat','Tea',2],
['What is "Mul"?','Rice','Tea','Water','Soup',3],
['"Kimchi" is?','Meat','Noodles','Fermented vegetables','Bread',3],
['What does "Gogi" mean?','Fish','Meat','Rice','Vegetable',2],
['When do you say "Jal meokgesseumnida"?','Leaving','Sleeping','Before eating','After eating',3]
]]
]
];

$inserted = 0;
foreach ($seed as $langName => $levels) {
    $lid = $langMap[$langName] ?? null;
    if (!$lid) continue;
    foreach ($levels as $lv) {
        $stmt = $pdo->prepare("INSERT INTO levels (language_id, level_number, title, content) VALUES (?, ?, ?, ?)");
        $words = [];
        foreach ($lv[2] as $w) {
            $words[] = ['word' => $w[0], 'meaning' => $w[1], 'example' => $w[2]];
        }
        $stmt->execute([$lid, $lv[0], $lv[1], json_encode($words, JSON_UNESCAPED_UNICODE)]);
        $newLevelId = $pdo->lastInsertId();
        foreach ($lv[3] as $q) {
            $pdo->prepare("INSERT INTO questions (level_id, question, option1, option2, option3, option4, correct_option) VALUES (?, ?, ?, ?, ?, ?, ?)")
                ->execute([$newLevelId, $q[0], $q[1], $q[2], $q[3], $q[4], $q[5]]);
        }
        $inserted++;
    }
}

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><style>
body{font-family:sans-serif;background:#0f0a1a;color:#e2e8f0;display:flex;justify-content:center;align-items:center;min-height:100vh;margin:0;}
.box{background:#1a1333;border-radius:16px;padding:48px;text-align:center;box-shadow:0 0 40px rgba(124,58,237,.3);}
h1{color:#a78bfa;margin-bottom:16px;} a{color:#7c3aed;font-weight:600;}
</style></head><body><div class='box'>
<h1>✅ Database Seeded!</h1>
<p>$inserted levels with quiz questions inserted across all 8 languages.</p>
<p style='margin-top:24px;'><a href='dashboard.php'>→ Go to Dashboard</a></p>
</div></body></html>";
?>
