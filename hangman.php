<?php
// Start a session where to store the current state of the game
session_start();

// This is a class where we will create an instance for every new game.
// This will keep track the word to guess and the current state of the man being hanged
class Game {
	private $state;
	private $secretWord;
	private $progressWord;

	// Construct a new game
	public function Game()  {
		// Load a list of words from file
		$words = file("wordlist.txt");

		// Randomly select a word from the list as the secret word
		$this->secretWord = trim($words[array_rand($words, 1)]);
		$this->state = 0;

		// The progress word will be a bunch of underscore characters
		$this->progressWord = "";

		for($i = 0; $i < strlen($this->secretWord); $i++)
			$this->progressWord .= "_";
	}

	// Get the secret word
	public function getSecretWord() {
		return $this->secretWord;
	}

	// Draw hangman based on the current state
	public function drawHangman() {
		echo "<img src='".$this->state.".png' />";
	}

	// Write out the progress of the player displaying the letters that was
	// guessed and still need to guess
	public function drawProgressWord() {
		for($i = 0; $i < strlen($this->progressWord); $i++) 
			echo $this->progressWord[$i]." ";
	}

	// Attempt to apply a guess to the secret word, return true if it there is, otherwise false.
	// the progress word will get updated. If guess is wrong then the state is increased.
	public function applyGuess($letter) {
		$found = false;

		for($i = 0; $i < strlen($this->secretWord); $i++) {
			if($this->secretWord[$i] == $letter) {
				$found = true;
				$this->progressWord[$i] = $letter;
			}
		}

		if(!$found)
			$this->state++;

		return $found;
	}

	// Check if the player has won the game and that is if the progress word has been completely guessed
	public function hasWon() {
		return $this->secretWord == $this->progressWord;
	}

	// Check if the player has lost the game, that is if it reached state 7, the last state
	public function hasLost() {
		return $this->state >= 7;
	}

	// Check if the game is over, that is if the user won or lost
	public function isOver() {
		return $this->hasWon() || $this->hasLost();
	}
}

// Automatically start a new game if there is no new game, if there is an existing game
// then continue on that game
if(!isset($_SESSION["game"])) {
	$game = new Game();
	$_SESSION["game"] = $game;
} 
	
$game = $_SESSION["game"];
$message = "";

if(isset($_POST["input_submit"])) {
	// Handle the submit button, we get the entered letter
	$letter = strtolower($_POST["input_letter"]);

	// Make sure it is a letter
	if(strlen($letter) == 1 && ctype_alpha($letter)) {
		// Check if the game
		if($game->applyGuess($letter)) {
			$message = "Correct!";

			if($game->hasWon())
				$message .= " You win!";
		} else {
			$message = "Incorrect!";

			if($game->hasLost())
				$message .= " You lose, the word was '".$game->getSecretWord()."'";
		}

		// Delete the game from session if finished
		if($game->isOver())
			unset($_SESSION["game"]);
		else  
			$_SESSION["game"] = $game;
	} else {
		// Invalid input does not count
		$message = "Invalid input. Enter an Alphabet.";
	}
}

?>
<html>
<head>
	<title>Hangman</title>
	<style type="text/css">
		html * {
			padding: 0;
			margin: 0;
		}

		body {
			font-family: courier;
			font-size: 14px;
			margin-top: 50px;
			text-align: center;
		}

		table {
			border-collapse: collapse;
			margin-left: auto;
			margin-right: auto;
			margin-bottom: 10px
		}

		table td {
			border: solid 1px #ccc;
			padding: 10px;
		}

		input[type="text"] {
			font-family: courier;
			font-size: 14px;
			padding: 5px;
			border: solid 1px #000;
			width: 30px;
			outline: none;
		}

		input[type="submit"] {
			font-family: courier;
			font-size: 14px;
			padding: 10px 20px 10px 20px;
		}

		p {
			margin-bottom: 10px;
		}
	</style>
</head>
<body>
	<?php if($game->isOver()) { ?>
		<h1>Game Over</h1>
		<p><?php echo $message; ?></p>
		<p><a href="hangman.php">Start a new Game</a></p>
	<?php } else { ?>
		<form method="post" action="hangman.php">
			<h1>Hangman</h1>
			<p><?php echo $message; ?></p>
			<table>
				<tr>
					<td><?php $game->drawHangman(); ?></td>
					<td><?php $game->drawProgressWord(); ?></td>
				</tr>
			</table>
			<p>
				Guess a Letter: <input type="text" name="input_letter" />
			</p>
			<p>
				<input type="submit" name="input_submit" value="Submit" />
			</p>
		</form>
	<?php } ?>
</body>
</html>
