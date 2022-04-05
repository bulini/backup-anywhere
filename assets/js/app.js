(function ($) {
	$(document).ready(function($){
  
	  $('#backup_wp').click(function(e) {
		  e.preventDefault();
		  Swal.fire({
			title: 'Sei sicuro?',
			text: "Crea un file di backup di wp-content",
			icon: 'warning',
			showCancelButton: true,
			confirmButtonColor: '#3085d6',
			cancelButtonColor: '#d33',
			confirmButtonText: 'Si, Procedi!'
		  }).then((result) => {
			if (result.isConfirmed) {
			  $.ajax({
				url : app_data.ajaxurl,
				type : 'post',
				data : {action: 'zip_folders'},
				success : function( response ) {
				 //$('#load').addClass('loader-removed');
				  console.log(response);
				  Swal.fire(
					'Backup finito!',
					'sei contento?',
					'success'
				  )
				  //swal("Aggiornamento dati eseguito con successo!", "", "success");
				}
			  });
			}
		  })				  
		});

		$('#backup_db').click(function(e) {
			e.preventDefault();
			Swal.fire({
			  title: 'Sei sicuro?',
			  text: "Crea un file dump del database",
			  icon: 'warning',
			  showCancelButton: true,
			  confirmButtonColor: '#3085d6',
			  cancelButtonColor: '#d33',
			  confirmButtonText: 'Si, Procedi!'
			}).then((result) => {
			  if (result.isConfirmed) {
				$.ajax({
				  url : app_data.ajaxurl,
				  type : 'post',
				  data : {action: 'backup_database'},
				  success : function( response ) {
				   //$('#load').addClass('loader-removed');
					console.log(response);
					Swal.fire(
					  'Backup finito!',
					  'sei contento?',
					  'success'
					)
					//swal("Aggiornamento dati eseguito con successo!", "", "success");
				  }
				});
			  }
			})				  
		  });		
  
	});
  })(jQuery);