;(function($) {

	$.fn.loadTableOptions = [];
	
	$.fn.getTable = function(id) {
		var allOptions = $.fn.loadTableOptions;
		for (var i=0; i<allOptions.length; i++){
			if (allOptions[i].tableId == id){
				return allOptions[i].table;
			}
		}
		return null;
	};
	
	$.fn.getOptions = function(id) {
		var allOptions = $.fn.loadTableOptions;
		for (var i=0 ; i<allOptions.length; i++){
			if (allOptions[i].tableId == id){
				return allOptions[i];
			}
		}
		return null;
	};
	
//	$.fn.dataTableExt.oApi.fnReloadAjax = function (oSettings, sNewSource, fnCallback) {
//		
//		if ( typeof sNewSource != 'undefined' ) {
//			oSettings.sAjaxSource = sNewSource;
//		}
//		this.fnClearTable(this);
//		this.oApi._fnProcessingDisplay(oSettings, true);
//		var that = this;
//		
//		$.getJSON(oSettings.sAjaxSource, {'sColumns': this.oApi._fnColumnOrdering(oSettings) }, function(json) {
//			/* Got the data - add it to the table */
//			for (var i=0; i<json.aaData.length; i++) {
//				that.oApi._fnAddData( oSettings, json.aaData[i] );
//			}
//			
//			oSettings.aiDisplay = oSettings.aiDisplayMaster.slice();
//			that.fnDraw(that);
//			that.oApi._fnProcessingDisplay(oSettings, false);
//			
//			/* Callback user function - for event handlers etc */
//			if (typeof fnCallback == 'function') {
//				fnCallback( oSettings );
//			}
//		});
//	};
	
	$.fn.dataTableExt.oApi.fnSetAjaxSource = function (oSettings, sNewSource, fnCallback, bStandingRedraw) {
	    if (typeof sNewSource != 'undefined' && sNewSource != null) {
	        oSettings.sAjaxSource = sNewSource;
	    }
	    /* Callback user function - for event handlers etc */
	    if (typeof fnCallback == 'function' && fnCallback != null) {
	        fnCallback( oSettings );
	    }
	};
	
	jQuery.fn.dataTableExt.oApi.fnSetFilteringDelay = function ( oSettings, iDelay ) {
		/*
		 * Type:        Plugin for DataTables (www.datatables.net) JQuery plugin.
		 * Name:        dataTableExt.oApi.fnSetFilteringDelay
		 * Version:     2.2.1
		 * Description: Enables filtration delay for keeping the browser more
		 *              responsive while searching for a longer keyword.
		 * Inputs:      object:oSettings - dataTables settings object
		 *              integer:iDelay - delay in miliseconds
		 * Returns:     JQuery
		 * Usage:       $('#example').dataTable().fnSetFilteringDelay(250);
		 * Requires:	  DataTables 1.6.0+
		 *
		 * Author:      Zygimantas Berziunas (www.zygimantas.com) and Allan Jardine (v2)
		 * Created:     7/3/2009
		 * Language:    Javascript
		 * License:     GPL v2 or BSD 3 point style
		 * Contact:     zygimantas.berziunas /AT\ hotmail.com
		 */
		var
			_that = this,
			iDelay = (typeof iDelay == 'undefined') ? 250 : iDelay;
		
		this.each( function ( i ) {
			$.fn.dataTableExt.iApiIndex = i;
			var
				$this = this, 
				oTimerId = null, 
				sPreviousSearch = null,
				anControl = $( 'input', _that.fnSettings().aanFeatures.f );
			
				anControl.unbind( 'keyup' ).bind( 'keyup', function() {
				var $$this = $this;

				if (sPreviousSearch === null || sPreviousSearch != anControl.val()) {
					window.clearTimeout(oTimerId);
					sPreviousSearch = anControl.val();	
					oTimerId = window.setTimeout(function() {
						$.fn.dataTableExt.iApiIndex = i;
						_that.fnFilter( anControl.val() );
					}, iDelay);
				}
			});
			
			return this;
		} );
		return this;
	};

    function trim(str) {
		str = str.replace(/^\s+/, '');
		for (var i = str.length - 1; i >= 0; i--) {
			if (/\S/.test(str.charAt(i))) {
				str = str.substring(0, i + 1);
				break;
			}
		}
		return str;
	}

	jQuery.fn.dataTableExt.oSort['date-euro-asc'] = function(a, b) {
		if (trim(a) != '') {
			var frDatea = trim(a).split(' ');
			var frTimea = frDatea[1].split(':');
			var frDatea2 = frDatea[0].split('/');
			var x = (frDatea2[2] + frDatea2[1] + frDatea2[0] + frTimea[0] + frTimea[1] + frTimea[2]) * 1;
		} else {
			var x = 10000000000000; // = l'an 1000 ...
		}

		if (trim(b) != '') {
			var frDateb = trim(b).split(' ');
			var frTimeb = frDateb[1].split(':');
			frDateb = frDateb[0].split('/');
			var y = (frDateb[2] + frDateb[1] + frDateb[0] + frTimeb[0] + frTimeb[1] + frTimeb[2]) * 1;		                
		} else {
			var y = 10000000000000;		                
		}
		var z = ((x < y) ? -1 : ((x > y) ? 1 : 0));
		return z;
	};

	jQuery.fn.dataTableExt.oSort['date-euro-desc'] = function(a, b) {
		if (trim(a) != '') {
			var frDatea = trim(a).split(' ');
			var frTimea = frDatea[1].split(':');
			var frDatea2 = frDatea[0].split('/');
			var x = (frDatea2[2] + frDatea2[1] + frDatea2[0] + frTimea[0] + frTimea[1] + frTimea[2]) * 1;		                
		} else {
			var x = 10000000000000;		                
		}

		if (trim(b) != '') {
			var frDateb = trim(b).split(' ');
			var frTimeb = frDateb[1].split(':');
			frDateb = frDateb[0].split('/');
			var y = (frDateb[2] + frDateb[1] + frDateb[0] + frTimeb[0] + frTimeb[1] + frTimeb[2]) * 1;		                
		} else {
			var y = 10000000000000;		                
		}		            
		var z = ((x < y) ? 1 : ((x > y) ? -1 : 0));		            
		return z;
	};
	
	$.fn.loadTable = function(customOptions) {
		
		if (!customOptions) {
			customOptions = {};
		}
		
		var _options = $.fn.loadTableOptions;
		var me = this;
		
		function optionsClass(customOptions, me) {
			this.successMessage = (typeof customOptions.successMessage == 'undefined') ? '' : customOptions.successMessage;
			this.editFormElement = (typeof customOptions.editFormElement == 'undefined') ? null : customOptions.editFormElement;
			this.fileAddForm = (typeof customOptions.fileAddForm == 'undefined') ? '' : customOptions.fileAddForm;
			this.fileEditForm = (typeof customOptions.fileEditForm == 'undefined') ? '' : customOptions.fileEditForm;
			this.editCallback = (typeof customOptions.editCallback == 'undefined') ? null : customOptions.editCallback;
			this.allowCreate = (typeof customOptions.allowCreate == 'undefined') ? true : customOptions.allowCreate;
			this.allowDelete = (typeof customOptions.allowDelete == 'undefined') ? true : customOptions.allowDelete;
			this.allowUpdate = (typeof customOptions.allowUpdate == 'undefined') ? true : customOptions.allowUpdate;
			this.delConfirmation = (typeof customOptions.delConfirmation == 'undefined') ? false : customOptions.delConfirmation;
			this.delConfirmationMsg = (typeof customOptions.delConfirmationMsg == 'undefined') ? 'Tem certeza que deseja excluir o(s) registro(s) selecionados?' : customOptions.delConfirmationMsg;
			
			//Custom table options:
			this.aoColumns = (typeof customOptions.aoColumns == 'undefined') ? [] : customOptions.aoColumns;
			this.fnDrawCallback = (typeof customOptions.fnDrawCallback == 'undefined') ? null : customOptions.fnDrawCallback;
			this.fnInitComplete = (typeof customOptions.fnInitComplete == 'undefined') ? null : customOptions.fnInitComplete;
			this.sAjaxSource = (typeof customOptions.sAjaxSource == 'undefined') ? '' : customOptions.sAjaxSource;
			this.sDom = (typeof customOptions.sDom == 'undefined') ? '<"top"ifr>t<"bottom"p><"clear">' : customOptions.sDom;
			this.iDisplayLength = (typeof customOptions.iDisplayLength == 'undefined') ? 20 : customOptions.iDisplayLength;
			this.aaSorting = (typeof customOptions.aaSorting == 'undefined') ? [[ 0, 'asc' ]] : customOptions.aaSorting;
			this.sPaginationType = (typeof customOptions.sPaginationType == 'undefined') ? 'full_numbers' : customOptions.sPaginationType;
			this.bServerSide = (typeof customOptions.bServerSide == 'undefined') ? true : customOptions.bServerSide;
			
			this.formTableElement = me.parent();
			this.tableDiv = null;
			this.tableId = null;
			this.table = null;
			this.statusTimeout = null;
		}
		
		return this.each(function() {
			
			var options = new optionsClass(customOptions, me);
			options.tableId = this.getAttribute('id');
			_options.push(options);
			
			
			function resetAjaxForm() {
				if (options.delConfirmation && !confirm(options.delConfirmationMsg)) {
					return false;
				}
				options.formTableElement.find('.status_' + options.tableId).html('<span class="loading"></span>');
			}

			function processDeleteError(XMLHttpRequest, textStatus, errorThrown) {
				options.formTableElement.find('.status_' + options.tableId).html(textStatus+':'+XMLHttpRequest.responseText);
			}

			function processDeleteResponse(response, status) {
				if (response.error == null) {
					options.formTableElement.find('.status_' + options.tableId).html('<span class="success">Registro(s) excluído(s) com sucesso!</span>');
					if (options.statusTimeout != null) {
						clearTimeout(options.statusTimeout);
					}
					options.statusTimeout = setTimeout("$.fn.getOptions('"+options.tableId+"').formTableElement.find('.status_" + options.tableId + "').html('')", 8000);
				}
				else {
					options.formTableElement.find('.status_' + options.tableId).html('<span class="error">'+response.error+'</span>');
				}
				$.fn.getTable(options.tableId).fnDraw();
				$("#" + options.tableId + " :checkbox:first").attr('checked', false);
				$('#del_button_' + options.tableId).attr('disabled','disabled');
				$('#del_button_' + options.tableId).addClass('disabled');
			}
			
			//Ajax form plugin: http://malsup.com/jquery/form/
			var optionsDelete = {
		        beforeSubmit:  resetAjaxForm,  		   // pre-submit callback 
		        success:       processDeleteResponse,  // post-submit callback 
		 		error: 		   processDeleteError,
		 		dataType:	   'json'
		        //É possível adicionar outras opções da funcão Ajax do JQuery (http://docs.jquery.com/Ajax/jQuery.ajax)
		    };
			
			if (options.allowDelete == true) {
				if (options.formTableElement.is('form')) {
					options.formTableElement.ajaxForm(optionsDelete);
				}
				else {
					alert('A tabela deve estar dentro de um formulário');
				}
			}
			
			/* Get the rows which are currently selected */ 
			function fnGetSelected(oTableLocal) {
				var aReturn = new Array();
				var aTrs = oTableLocal.fnGetNodes();
				
				for (var i=0; i<aTrs.length; i++ ) {
					if ($(aTrs[i]).hasClass('row_selected') && $(aTrs[i]).is(':visible')) {
						aReturn.push(aTrs[i]);
					}
				}
				return aReturn;
			}
			
			//Datatables plugin: http://www.datatables.net/
			var dataTableOptions = {
				'oLanguage': {
					'sProcessing': 'Carregando...',
					'sLengthMenu': 'Exibir _MENU_ resultados',
					'sZeroRecords': 'Nenhum resultado encontrado',
					'sInfo': 'Exibindo _START_ a _END_ de _TOTAL_ resultados',
					'sInfoEmpty': 'Nenhum resultado',
					'sInfoFiltered': '(filtrados de _MAX_ resultados)',
					'sInfoPostFix': '',
					'sSearch': 'Procurar:',
					'sUrl': '',
					'oPaginate': {
						'sFirst':    'Primeira',
						'sPrevious': 'Anterior',
						'sNext':     'Próxima',
						'sLast':     'Última'
					}
				},
				'sPaginationType': options.sPaginationType,
				'bAutoWidth': false,
				'fnDrawCallback': function() { 
					bindEvents();
					if (options.fnDrawCallback) {
						options.fnDrawCallback();
					}
				},
				'bLengthChange': false,
				'bProcessing': true,
				'bServerSide': options.bServerSide,
				/*'fnServerData': function ( sSource, aoData, fnCallback ) {
					            $.ajax( {
					                'type': 'POST',
					                'url': sSource,
					                'data': aoData,
					                'success': function (msg) {
						                alert(msg);
						            }
					            } );
					        }*/
				'aaSorting': options.aaSorting,
				'iDisplayLength': options.iDisplayLength,
				'sDom': options.sDom,
				'fnInitComplete': function() {
					
					var del_button = '<input id="del_button_'+options.tableId+'" type="submit" value="Excluir selecionados" disabled="disabled" class="disabled" /> ';
					var add_button = '<input id="add_button_'+options.tableId+'" type="button" value="Cadastrar" />';
					var toolbar_footer = '';
					
					if (options.allowDelete) {
						toolbar_footer+= del_button;
					}
					if (options.allowCreate) {
						toolbar_footer+= add_button;
					}
					
					options.formTableElement.find('.bottom').prepend(toolbar_footer + '<span class="status_' + options.tableId + '"></span>');
					
					if (options.allowCreate) {
						$('#add_button_' + options.tableId).click(function() {
							window.location = options.fileAddForm;
						});
					}
					if (options.allowDelete) {
						$('#' + options.tableId + ' :checkbox:first').click(function() {
					    	if ($(this).is(':checked')) {
					    		$('#' + options.tableId + ' :checkbox').attr('checked', true);
			    				$('#' + options.tableId + ' tr').addClass('row_selected');
					    	}
					    	else {
					    		$('#' + options.tableId + ' :checkbox').attr('checked', false);
			    				$('#' + options.tableId + ' tr').removeClass('row_selected');
					    	}
					    });
					}
					
					$('#' + options.tableId).show();
					$('.dataTables_wrapper').fadeIn();
					
					if (options.fnInitComplete) {
						options.fnInitComplete();
					}
				}
			};
			
			if (options.aoColumns.length) {
				$.extend(dataTableOptions, {
					'aoColumns': options.aoColumns
				});
			}
			
			if (options.sAjaxSource) {
				$.extend(dataTableOptions, {
					'sAjaxSource': options.sAjaxSource
				});
			}
			
			options.table = $('#' + options.tableId).dataTable(dataTableOptions);
			
			$.fn.getTable(options.tableId).fnSetFilteringDelay(300);
			
			function bindEvents() {
				if (options.allowDelete) {
					$('#' + options.tableId + ' :checkbox').change(function(event) {
						if ($(this).is(':checked')){
							if ($('#' + options.tableId + ' :checkbox').size() > 1 || $(this).attr('name') != 'check_all') {
								$(event.target.parentNode.parentNode).addClass('row_selected');
								$('#del_button_' + options.tableId).removeAttr('disabled');
								$('#del_button_' + options.tableId).removeClass('disabled');
							}
						}
						else {
							$(event.target.parentNode.parentNode).removeClass('row_selected');
							if (($('.row_selected').size() == 1 && $('#' + options.tableId + ' :checkbox:first').is(':checked'))) {
								$('#' + options.tableId + ' :checkbox').attr('checked', false);
			    				$('#' + options.tableId + ' tr').removeClass('row_selected');
							}
							if ($('.row_selected').size() == 0) {
								$('#del_button_' + options.tableId).attr('disabled','disabled');
								$('#del_button_' + options.tableId).addClass('disabled');
							}
						}
					});

				}
				if (options.allowUpdate) {
					var checkboxes = $('#' + options.tableId + ' tbody td:first-child :checkbox');
					if (checkboxes.size() > 0) {
						$('#' + options.tableId + ' tbody td:not(:first-child)').each(function(index) {
							//Checkbox ID
							var id = '';
							try {
								//Checkbox ID
								id = $(this).parent().children().filter(':first-child').children().filter(':checkbox').get(0).value;
							}
							catch (e) {
								//TD ID
								id = $(this).attr('id');
								if (!id) {
									//TR ID
									id = $(this).parent().attr('id');
								}
							}
							if (id) {
								if (options.fileEditForm.search(/\?/) != -1) {
									var editUrl = options.fileEditForm + '&id='+id;
								}
								else {
									var editUrl = options.fileEditForm + '?id='+id;
								}
								$(this).html('<a href="'+editUrl+'">' + $(this).html() + '&nbsp;</a>');
							}
							else {
								//alert('Nenhum ID encontrado');
							}
			    		});
					}
					else {
						$('#' + options.tableId + ' tbody td').each(function(index) {
							var id = '';
							try {
								//Checkbox ID
								id = $(this).parent().children().filter(':first-child').children().filter(':checkbox').get(0).value;
							}
							catch (e) {
								//TD ID
								id = $(this).attr('id');
								if (!id) {
									//TR ID
									id = $(this).parent().attr('id');
								}
							}
							if (id) {
								if (options.fileEditForm.search(/\?/) != -1) {
									var editUrl = options.fileEditForm + '&id='+id;
								}
								else {
									var editUrl = options.fileEditForm + '?id='+id;
								}
								$(this).html('<a href="'+editUrl+'">' + $(this).html() + '&nbsp;</a>');
							}
							else {
								//alert('Nenhum ID encontrado');
							}
			    		});
					}
					
					/* OLD:
					$('#' + options.tableId + ' tbody tr').mousedown(function(event) {
						if ($(event.target).is(':not(:first-child)') || $(this).children().size() == 1) {
							//console.log($(event.target).parent().children().size());
							if ($(this).children().size() == 1) {
								var id = $(event.target).attr('id');
							}
							else {
								var id = this.childNodes[0].childNodes[0].value;
							}
							if (id) {
								if (options.fileEditForm.search(/\?/) != -1) {
									var editUrl = options.fileEditForm + '&id='+id;
								}
								else {
									var editUrl = options.fileEditForm + '?id='+id;
								}
								if (event.which == 1) {
									window.location = editUrl;
								}
								else {
									window.open(editUrl);
								}
							}
							else {
								alert('Nenhum ID encontrado');
							}
							return false;
						}
					});
					*/
				}
			};
		});
	};
})(jQuery);