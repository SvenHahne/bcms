/*
 jquery script for responsive multi-columns layouts
 that are back compatible (ie8, etc.)
 
 takes the elements within a node and distributes them into columns
 */

(function($) 
{
	// placeholder for global variables
	$.reColumn = {
		idNr : [],
		idCounter : 0,
		selectors : [],
		opts : []
	};
 
	$.fn.reColumn = function( options ) 
	{
		$.reColumn.opts.push( $.extend({}, $.fn.reColumn.defaults, options) ); 
		$.reColumn.selectors.push( this.selector );
		$.reColumn.idNr.push( $.reColumn.idCounter );
 
		$.reColumn.resize = false;
		$.fn.reColumn.replaceWithCol( $.reColumn.idCounter );
		
		$.reColumn.resize = true;
		$( window ).resize( $.reColumn.idCounter, $.fn.reColumn.replaceWithCol );
		$.reColumn.idCounter++;
	};

	$.fn.reColumn.defaults = {
		nrColsPtr: ['1', '2', '3', '3', '3'],
		nrColsLnd: ['2', '2', '3', '3', '3'],
		targetWidths: ['450', '768', '1024', '1500'],
		verticalAlign: 'top',
		width: '100%',
		height: '',
		cellStyle: ''
	};
 
	$.fn.reColumn.replaceWithCol = function( nr ) 
	{
		var width = window.innerWidth;
		var ind=0, actNrCols;
		var colSel, selId;

		if ( nr.type == "resize" )
		{
			selId = nr.data;
		} else {
			selId = nr;
		}
 
		var opts = $.reColumn.opts[selId];
		var baseNode = $( $.reColumn.selectors[selId] );
		var actSel = $.reColumn.selectors[selId];
 
		if ( baseNode.length > 0 )
		{
			if ( window.innerWidth > window.innerHeight )
			{
				colSel = opts.nrColsLnd;
			} else {
				colSel = opts.nrColsPtr;
			}

			actNrCols = colSel[0];

			// get nrCols depending on the actual windowWidth
			while ( opts.targetWidths[ind] < width && ind <= opts.targetWidths.length  )
			{
				ind++;
				actNrCols = colSel[ind];
			}  

			// create the base table div
			var $newBaseNode = $("<div style='display:table;table-layout:fixed;width:"+opts.width+";height:"+opts.height+";' />");

			// if selector is class or id, add it
			if (actSel.charAt(0) == ".")
			{
				var selSplit = actSel.split(".");
				$newBaseNode.addClass(selSplit[1]);
			} else if (actSel.charAt(0) == "#") 
			{
				var selSplit = actSel.split("#");
				$newBaseNode.attr('id', selSplit[1]);
			}
	 
			var strId = ""+Math.floor(selId / 10)+(selId % 10);
			$newBaseNode.attr("id", "reColBase"+strId);

			// add column cells
			var cols = new Array();
			for (var i=0;i<actNrCols;i++)
			{
				cols.push( $("<div class='"+opts.cellStyle+" reColCell"+strId+"' style='display:table-cell;vertical-align:"+opts.verticalAlign+"' />") );
			}

			$newBaseNode.append(cols);

			var elems = new Array();
			if ($.reColumn.resize)
			{
				// if resize collect the elements from within the existing columns
				var nrElem = $( ".reColItem"+strId ).length / actNrCols;
				$(".reColCell"+strId).each(function(i, cell)
				{
					$.each( $(cell).children(), function(j, subNode)
					{
						elems.push(subNode);
					});
				});
	 
				$.each( elems, function(i, subNode) 
				{
					cols[ Math.floor(i / nrElem) ].append(subNode);
				});
	 
				$( $.reColumn.selectors[selId] ).replaceWith($newBaseNode);
	 
			} else 
			{
				// if started the first time, take the elements as they are
				var nrElem = $(actSel).children().length / actNrCols;
				$.each( $(actSel).children(), function(i, subNode) 
				{
					$(subNode).addClass( "reColItem"+strId );
					cols[ Math.floor(i / nrElem) ].append(subNode);
				});
	 
				baseNode.replaceWith($newBaseNode);
			}
		}
	};
}(jQuery));