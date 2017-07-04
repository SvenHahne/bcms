<?php
	
	class JPlayer {

		public $jsPath;
		public $skinPath;
		public $enableVideo;
		public $rootPath;
		public $posterPath;
		public $fileList;
		
		public $videoDispStyleName;
		public $playerWidth;
		public $posterHeight;
		public $albumTitleDivName;
		
		public $highlightColor;
		public $bgColor;
		
		public $type;
		public $playerNr;
		public $cssClass;
		public $showAlbumName;
		public $swfFallBackPath;

		function __construct( $_type, $_jsPath, $_skinPath, $_rootPath, $_posterPath, $_fileList, 
						 	$_playerWidth, $_posterHeight, $_playerNr, $_cssClass, $_showAlbumName, 
						 	$_swfFallBackPath, $_readyFunction ) 
		{
			$this->type = $_type; // 'single-video', 'playlist-audio', 'playlist'

			if ( $this->type == "playlist" ) {
				$this->enableVideo = true;
			}
			$this->playerNr = $_playerNr;
			$this->cssClass = $_cssClass;
			$this->jsPath = $_jsPath;
			$this->skinPath = $_skinPath;
			$this->enableVideo = false;
			$this->fileList = $_fileList;
			$this->rootPath = $_rootPath;
			$this->posterPath = $_posterPath;
			$this->videoDispStyleName = "jp-video-270p";
			$this->playerWidth = $_playerWidth;
			$this->posterHeight = $_posterHeight;
			$this->albumTitleDivName = "album_title";
			$this->highlightColor = "#AD0304";
			$this->bgColor = "rgb(235, 148, 18)";
			$this->showAlbumName = $_showAlbumName;
			$this->swfFallBackPath = $_swfFallBackPath;
			$this->readyFunction = $_readyFunction;
		}
		
		function addLinksToHead() {
			print "<script type='text/javascript' src='".$this->jsPath."/jquery.jplayer.min.js'></script>";
			print "<script type='text/javascript' src='".$this->jsPath."/jplayer.playlist.min.js'></script>";
			print "<link href='".$this->skinPath."' rel='stylesheet' type='text/css' />";
		}

//***************************************************************
//*																*
//*			bau den jquery - source code ein						*
//*																*
//***************************************************************		
		
		function addJquerySrc() {

// -------------------- styles playlist-audio --------------------------------------

			if ( $this->type == 'playlist-audio' ) {
				
				print '<style type="text/css">
	div.'.$this->videoDispStyleName.' {
		width:'.$this->playerWidth.'px;
	}
	div.jp-video {
		color: #666;
		border:1px solid '.$this->highlightColor.';
		background-color:'.$this->bgColor.';
	}
	div.jp-playlist {
		background-color:rgb(254, 153, 1);
		border-top:1px solid '.$this->highlightColor.';
	}
	div.jp-playlist li {
		border-bottom:1px solid '.$this->bgColor.';
	}
	div.jp-type-playlist div.jp-playlist a:hover {
		color:'.$this->highlightColor.';
	}
	div.jp-type-playlist div.jp-playlist a.jp-playlist-current {
		color:'.$this->highlightColor.';
	}
	span.jp-artist {
		font-size:.8em;
		color:#222;
	}
</style>';

// -------------------- styles playlist --------------------------------------

			} elseif ( $this->type == 'playlist' ) {
				print '<style type="text/css">
	div.'.$this->videoDispStyleName.' {
		width:'.$this->playerWidth.'px;
	}
</style>';
			}
			
			
// -------------------- playlist code für playlist-audio und playlist --------------------------------------

			if ( $this->type == 'playlist-audio' ) {

				print '<script type="text/javascript">
$(document).ready(function(){
	var rawUrl = []; 
				var albumNames = [];
				';
			
				foreach ( $this->fileList as $key => $value ) {
					print 'rawUrl.push("'.$this->rootPath.'/'.$value[0].'");';
					print 'albumNames.push("'.$value['album'].'");';
				}
		
				print 'var myPlaylist = new jPlayerPlaylist({
										jPlayer: "#jquery_jplayer_'.$this->playerNr.'",
										cssSelectorAncestor: "#jp_container_'.$this->playerNr.'"
										}, [], {
											playlistOptions: {
												enableRemoveControls: false
											},
											swfPath: "'.$this->swfFallBackPath.'",
											supplied: "ogv, m4v, oga, mp3, flv",
											size: {
												width: "'.$this->playerWidth.'px",
												height: "'.$this->posterHeight.'px"
											},
											play: function(event) {
												$("#'.$this->albumTitleDivName.'").text( albumNames[ jQuery.inArray( event.jPlayer.status.src, rawUrl ) ] );
											},
											ready: function(event) {
												'.$this->readyFunction.'
											}
										});
			
	// Audio mix playlist
	myPlaylist.setPlaylist([';
				$it = 0;
				foreach ( $this->fileList as $key => $value ) {
					$ending = explode( '.', (string) $value[0] );
					print '{
		title:"'.$value['title'].'",
		artist:"'.$value['artist'].'",
		'.$ending[1].':"'.$this->rootPath.'/'.$value[0].'",
		poster: "'.$this->rootPath.'/'.$value['image'].'"
		}';
					if ( $it < sizeof($this->fileList)-1 ) print ',';
					$it++;
				}
				print ']);
});
	</script>';
			}

// -------------------- plalyist video/audio --------------------------------------
			
			
			elseif ( $this->type == 'playlist' ) {
				
				print '<script type="text/javascript">
	$(document).ready(function(){
	var rawUrl = []; 
	var albumNames = [];
';
				
				foreach ( $this->fileList as $key => $value ) {
					print 'rawUrl.push("'.$this->rootPath.'/'.$value[0].'");';
					print 'albumNames.push("'.$value['album'].'");';
				}
				
				print 'var myPlaylist = new jPlayerPlaylist({
					jPlayer: "#jquery_jplayer_'.$this->playerNr.'",
					cssSelectorAncestor: "#jp_container_'.$this->playerNr.'"
				}, [], {
					playlistOptions: {
					enableRemoveControls: false
				},
				swfPath: "'.$this->swfFallBackPath.'",
				supplied: "ogv, m4v, oga, mp3, flv",
				size: {
				width: "'.$this->playerWidth.'px",
				height: "'.$this->posterHeight.'px"
				},
				play: function(event) {
				$("#'.$this->albumTitleDivName.'").text( albumNames[ jQuery.inArray( event.jPlayer.status.src, rawUrl ) ] );
				},
				ready: function(event) {
				'.$this->readyFunction.'
				}
				});
				
				// Audio Video playlist
				myPlaylist.setPlaylist([';
				$it = 0;
				foreach ( $this->fileList as $key => $value ) {
					$ending = explode( '.', (string) $value[0] );
					print '{
					title:"'.$value['title'].'",
					artist:"'.$value['artist'].'",
					m4v:"'.$this->rootPath.'/'.$ending[0].'.m4v",
					ogv: "'.$this->rootPath.'/'.$ending[0].'.ogv",';
					//webmv: "'.$this->rootPath.'/'.$ending[0].'.webm",
					print 'poster: "'.$this->rootPath.'/'.$value['image'].'"
					}';
					if ( $it < sizeof($this->fileList)-1 ) print ',';
					$it++;
				}
				print ']);
				});
				</script>';			
			}
			
			
// -------------------- single-video --------------------------------------

			elseif ( $this->type == 'single-video' ) {
				print '
<script type="text/javascript">
	$(document).ready(function(){
								$("#jquery_jplayer_'.$this->playerNr.'").jPlayer({
									ready: function () {
										$(this).jPlayer("setMedia", {
											m4v: "'.$this->rootPath.'/'.$this->fileList.'.m4v",
											ogv: "'.$this->rootPath.'/'.$this->fileList.'.ogv",';
											//webmv: "'.$this->rootPath.'/'.$this->fileList.'.webm",
											print 'poster: "zk/pic/video_loop.jpg"
										});
										$(this).jPlayer("play");
										//'.$this->readyFunction.'
									},
									loop: "true",
									swfPath: "zk/lib/",
									supplied: "ogv, m4v",
									size: {
										width: "'.$this->playerWidth.'px",
										height: "'.$this->posterHeight.'px"
									}
								});				  
	});
</script>
';
			}
		}
		
		
		
//***************************************************************
//*																*
//*			bau den html code ein								*
//*																*
//***************************************************************		
		
		
		function addHtml() {


// -------------------- playlist-audio --------------------------------------
			
			if ( $this->type == 'playlist-audio' || $this->type == 'playlist' ) 
			{
				$this->enableVideo = true;
				
				print '<div id="jp_container_'.$this->playerNr.'" style="overflow:hidden;" class="'.$this->cssClass.' jp-video '.$this->videoDispStyleName.'">
			<div class="jp-type-playlist">';
				
				// schmutziger hack um den album namen anzeigen zu lassen
				print '<div id="jquery_jplayer_'.$this->playerNr.'" class="jp-jplayer"';

				if ( $this->showAlbumName == true ) print ' style="float:left;width:60px;height:160px;"';
				if ( $this->type == 'playlist' ) print ' style="width:'.$this->playerWidth.'px;height:160px;"';

				print '></div>';

				if ( $this->showAlbumName == true ) {
					print '<div id="'.$this->albumTitleDivName.'" style="font-size:13px;height:'.$this->posterHeight.'px;padding-left:70px;"></div>';
				}
					print '<div class="jp-gui">';
				
				if ( $this->enableVideo == true ) {
//					print '<div class="jp-video-play">
//								<a href="javascript:;" class="jp-video-play-icon" tabindex="1">play</a>
//							</div>';
				}
				print '<div class="jp-interface">
							<div class="jp-progress">
							<div class="jp-seek-bar">
								<div class="jp-play-bar"></div>
							</div>
						</div>
						<div class="jp-current-time"></div>
						<div class="jp-duration"></div>
						<div class="jp-title">
							<ul>
								<li></li>
							</ul>
						</div>
						<div class="jp-controls-holder">
							<ul class="jp-controls">
								<li><a href="javascript:;" class="jp-previous" tabindex="1">previous</a></li>
								<li><a href="javascript:;" class="jp-play" tabindex="1">play</a></li>
								<li><a href="javascript:;" class="jp-pause" tabindex="1">pause</a></li>
								<li><a href="javascript:;" class="jp-next" tabindex="1">next</a></li>
								<li><a href="javascript:;" class="jp-stop" tabindex="1">stop</a></li>
								<li><a href="javascript:;" class="jp-mute" tabindex="1" title="mute">mute</a></li>
								<li><a href="javascript:;" class="jp-unmute" tabindex="1" title="unmute">unmute</a></li>
								<li><a href="javascript:;" class="jp-volume-max" tabindex="1" title="max volume">max volume</a></li>
							</ul>
							<div class="jp-volume-bar">
								<div class="jp-volume-bar-value"></div>
							</div>';				
				if ( $this->enableVideo == true ) {

							print '<ul class="jp-toggles">
								<li><a href="javascript:;" class="jp-full-screen" tabindex="1" title="full screen">full screen</a></li>
								<li><a href="javascript:;" class="jp-restore-screen" tabindex="1" title="restore screen">restore screen</a></li>
								<li><a href="javascript:;" class="jp-shuffle" tabindex="1" title="shuffle">shuffle</a></li>
								<li><a href="javascript:;" class="jp-shuffle-off" tabindex="1" title="shuffle off">shuffle off</a></li>
								<li><a href="javascript:;" class="jp-repeat" tabindex="1" title="repeat">repeat</a></li>
								<li><a href="javascript:;" class="jp-repeat-off" tabindex="1" title="repeat off">repeat off</a></li>
							</ul>';
				}
						print '</div>
					</div>
				</div>
				<div class="jp-playlist">
					<ul>
					<!-- The method Playlist.displayPlaylist() uses this unordered list -->
						<li></li>
					</ul>
				</div>
				<div class="jp-no-solution">
					<span>Update Required</span>
					To play the media you will need to either update your browser to a recent version or update your <a href="http://get.adobe.com/flashplayer/" target="_blank">Flash plugin</a>.
				</div>
			</div>
		</div>';

// -------------------- single-video --------------------------------------
				
			} elseif (  $this->type == 'single-video' ) {
				print '<div style="width:'.($this->playerWidth-1).'px;overflow:hidden;" class="'.$this->cssClass.'">';
					print '<div id="jquery_jplayer_'.$this->playerNr.'" /></div>';
				print '</div>';
			}
		}
	}
?>