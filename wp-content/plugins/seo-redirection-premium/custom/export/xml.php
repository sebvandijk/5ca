<?php

// setting up PDO
$dbLocation = 'mysql:dbname=db001;host=localhost';
$dbUser = 'user';
$dbPass = 'password';
$db = new PDO($dbLocation, $dbUser, $dbPass);

// prepare all queries...
$dbArtists = $db->prepare("SELECT * FROM artist");
$dbAlbums =  $db->prepare("SELECT * FROM album WHERE artist_ID=:artist_id");
$dbSongs =   $db->prepare("SELECT * FROM song WHERE album_ID=:album_id");

// fetch all artists
$dbArtists->execute();
$artists=$dbArtists->fetchAll(PDO::FETCH_ASSOC);


$x=new XMLWriter();
$x->openMemory();
$x->startDocument('1.0','UTF-8');
$x->startElement('music');

foreach ($artists as $artist) {

    $x->startElement('artist');
    $x->writeAttribute('name',$artist['artist']);

    // fetch all albums of this artist
    $dbAlbums->execute(array(':artist_id' => $artist['artist_id']));
    $albums = $dbAlbums->fetchAll(PDO::FETCH_ASSOC);

    foreach ($albums as $album) {

        $x->startElement('album');
        $x->writeAttribute('name',$album['album']);

        // fetch all songs from this album
        $dbSongs->execute(array(':album_id' => $album['album_id']));
        $songs = $dbSongs->fetchAll(PDO::FETCH_ASSOC);

        foreach ($songs as $song) {

            $x->startElement('song');
            $x->text($song['song']);
            $x->endElement(); // song
        } // foreach $songs

        $x->endElement(); // album
    } // foreach $albums

    $x->endElement(); // artist
} // foreach $artists

$x->endElement(); // music
$x->endDocument();
$xml = $x->outputMemory();