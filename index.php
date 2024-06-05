<?php
$apiKey = "1f1bb9f66bb326cf2b6a4d3ea84cfbdb";

$apiUrlTopTracks = "http://ws.audioscrobbler.com/2.0/?method=chart.gettoptracks&api_key=$apiKey&limit=10&format=xml";
$responseTopTracks = file_get_contents($apiUrlTopTracks);
$xmlTopTracks = simplexml_load_string($responseTopTracks);

$topTracks = [];
foreach ($xmlTopTracks->tracks->track as $track) {
	$trackInfo = [
		"name" => (string)$track->name,
		"artist" => (string)$track->artist->name,
		"url" => (string)$track->url
	];

	$topTracks[] = $trackInfo;
}

$searchResults = [];
$searchQuery = "";
if (isset($_GET["query"])) {
	$searchQuery = $_GET["query"];
	$encodedQuery = urlencode($searchQuery);
	$apiUrlSearch = "http://ws.audioscrobbler.com/2.0/?method=track.search&track=$encodedQuery&api_key=$apiKey&format=xml";
	$responseSearch = file_get_contents($apiUrlSearch);

	libxml_use_internal_errors(true);
	$xmlSearchResults = simplexml_load_string($responseSearch, "SimpleXMLElement", LIBXML_NOCDATA);

	if ($xmlSearchResults === false) {
		echo "Neuspjesno ucitavanje XML\n";
		foreach (libxml_get_errors() as $error) {
			echo "\t", $error->message;
		}
	} else {
		$xmlSearchResults->registerXPathNamespace('opensearch', 'http://a9.com/-/spec/opensearch/1.1/');
		$searchResults = $xmlSearchResults->xpath('//trackmatches/track');
	}
}
?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">

	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
	<link rel="stylesheet" href="css.css">

	<title>MusicMatch</title>

	<style>
		.hidden {
			display: none;
		}
	</style>
</head>
<body>
	<nav class="navbar navbar-expand-lg">
		<a class="navbar-brand mx-auto" href="./index.php">MusicMatch</a>
	</nav>

	<div class="search-bar-container" id="search-bar-container">
		<form method="GET" action="./index.php" class="search-bar">
			<input type="text" name="query" placeholder="Pretraži pjesme" autocomplete="off" autocapitalize="off" spellcheck="false">
			<button type="submit">
				<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
					<path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"></path>
				</svg>
			</button>
		</form>
	</div>

	<div class="container">
		<?php if (isset($_GET['query'])) : ?>

			<script>
				document.getElementById("search-bar-container").classList.add("hidden");
			</script>

			<div class="btn-container">
				<a href="index.php" class="btn-back"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-arrow-left-circle-fill" viewBox="0 0 16 16">
						<path d="M8 0a8 8 0 1 0 0 16A8 8 0 0 0 8 0m3.5 7.5a.5.5 0 0 1 0 1H5.707l2.147 2.146a.5.5 0 0 1-.708.708l-3-3a.5.5 0 0 1 0-.708l3-3a.5.5 0 1 1 .708.708L5.707 7.5z" />
					</svg>&nbsp Nazad na pretraživanje</a>
			</div>
			<h2 class="search-result-title">Rezultati pretrage "<?php echo htmlspecialchars($_GET["query"]); ?>"</h2>

			<ul class="list-group mb-5">
				<?php
				if (!empty($xmlSearchResults->results->trackmatches->track)) {
					foreach ($xmlSearchResults->results->trackmatches->track as $track) {
						$trackName = htmlspecialchars($track->name);
						$artistName = htmlspecialchars($track->artist);
						$trackUrl = htmlspecialchars($track->url);
						echo "<li class='list-group-item'><a href='$trackUrl' target='_blank'>$trackName - $artistName</a></li>";
					}
				} else {
					echo "<li class='list-group-item'>Nema rezultata.</li>";
				}
				?>
			</ul>
		<?php else : ?>
			<h2 class="text-center top-title">Top 10 Lista</h2>
			<div class="top-tracks">
				<?php
				foreach ($topTracks as $index => $track) {
					$trackName = htmlspecialchars($track['name']);
					$artistName = htmlspecialchars($track['artist']);
					$trackUrl = htmlspecialchars($track['url']);
					echo "<div class='track-item'>
                            <div class='track-info'>
                                <a href='$trackUrl' target='_blank' class='track-name'>$trackName</a>
                                <div class='artist-name'>$artistName</div>
                            </div>
                          </div>";
				}
				?>
			</div>
		<?php endif; ?>
	</div>

	<footer>
		<p>Autor: Antonio Lozić</p>
	</footer>

	<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js" integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy" crossorigin="anonymous"></script>
</body>
</html>