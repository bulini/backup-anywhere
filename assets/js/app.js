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
        $('.loader').removeClass('loader-off');
			  $.ajax({
				url : app_data.ajaxurl,
				type : 'post',
				data : {action: 'zip_folders'},
				success : function( response ) {
          $('.loader').addClass('loader-off');
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
				$('.loader').removeClass('loader-off');
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
		
		$('#upload_bk').click(function(e) {
			e.preventDefault();
			Swal.fire({
			  title: 'Sei sicuro?',
			  text: "Vuoi caricare tutto il backup corrente sul server?",
			  icon: 'warning',
			  showCancelButton: true,
			  confirmButtonColor: '#3085d6',
			  cancelButtonColor: '#d33',
			  confirmButtonText: 'Si, Procedi!'
			}).then((result) => {
			  if (result.isConfirmed) {
				$('.loader').removeClass('loader-off');
				$.ajax({
				  url : app_data.ajaxurl,
				  type : 'post',
				  data : {action: 'upload_backup'},
				  success : function( response ) {
					$('.loader').addClass('loader-off');
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