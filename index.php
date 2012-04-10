<?php
require_once('voice_class.php');
if ($_POST['q']) {
	$post = (object)$_POST;
	$voice = new google_voice_api_class($post->q);
	$voice->get_voice();
}
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
    <head>
        <title>Leitor de Textos</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

		<link rel="stylesheet" href="css/styles.css" type="text/css" media="screen" charset="utf-8" />
		
		<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.1/jquery.min.js"></script>
		<script type="text/javascript" src="http://malsup.github.com/chili-1.7.pack.js"></script>
		<script type="text/javascript" src="http://github.com/malsup/media/raw/master/jquery.media.js?v0.92"></script>
		<script type="text/javascript" src="js/jquery.metadata.js"></script>

		<script type="text/javascript">
			$(document).ready(function(){
				$('a.media').media({width: 700, height: 20, autoplay: 0});
				$('input.clean').click(function(){
					$('#q').empty();
					$('#q').animate({height:'250px'});
				});
				
				$('#q').keyup(function(){
					var minimal_height = 100;
					var factor_minimal = 3
					var new_height = $(this).val().length/factor_minimal;
					if (new_height < minimal_height) new_height = minimal_height;
					$(this).animate({height:new_height+'px'})
				});
			});
		</script>
		
    </head>
    <body>

        <div id="page">
            <form method="POST" >
                <p>Digite um texto para ser lido:</p>
                <textarea id='q' name="q" ><?php echo $post->q ? stripslashes($post->q) : google_voice_api_class::get_sample_text()?></textarea>
				<input class='clean' type='reset' title='Limpar texto'/>
				<?php if (isset($voice)) {?>
					<a class="media" href="<?php echo $voice->get_file_name() ?>" title="Clique para Ouvir"><small>clique no 'Play' para ouvir</small></a>
				<?php } ?>
                <input type="submit" class="button" value="Processar texto" />
			</form>
			
			<br clear='all'/>
			<br />
			
			<div id="rodape">
				<p>Desenvolvido por <br /> <strong><a target='_blank' href='http://www.leonardomateus.com.br'>Leonardo Mateus</strong></p>
				<img src='img/poweredByGoogle2.png' title='Powered by Google' alt='Powered by Google' />
			</div>
			
        </div>
		
		

    </body>
</html>
