$(document).ready(function() {


	/*** SET HEIGHT BASED ON VIEW PORT ***/				
	function thirty_pc() {
	var height = $(window).height();
	var thirtypc = (100 * height) / 100;
	thirtypc = parseInt(thirtypc) + 'px';
	$(".right-bar").css('height',thirtypc);
	$(".menu").css('height',thirtypc);
	}
	$(document).ready(function() {
	thirty_pc();
	$(window).bind('resize', thirty_pc);
	});		

	/*** Account Toogle ***/	
	$('#account').click(function() {
	$('.account2').slideToggle('slow');
	return false;
	});
	
	/*** User Online Toogle ***/	
	$('#user-online').click(function() {
	$('.user-online2').slideToggle('slow');
	return false;
	});
	
	/*** Disk Usage Toogle ***/	
	$('#disk-usage').click(function() {
	$('.disk-usage').slideToggle('slow');
	return false;
	});
	
	/*** Pending Task Toogle ***/	
	$('#pending-task').click(function() {
	$('.pending-task').slideToggle('slow');
	return false;
	});
	
	/*** Notification Toogle ***/	
	$(".notification-btn").click( function(){
	$('.message').fadeOut();
	$(this).next('.notification').fadeToggle();
	});	
	
	/*** Message Toogle ***/	
	$(".message-btn").click( function(){
	$(this).next('.message').fadeToggle();
	$('.notification').fadeOut();
	});	
	
	/*** Upload Files Toogle ***/	
	$(".upload-btn").click( function(){
	$(this).next('.upload-files').fadeToggle();
	$('.message').fadeOut();
	$('.notification').fadeOut();
	});	

	
	/*** Responsive Menu  ***/
	$(".responsive-menu ul li").click( function(){
	$("ul",this).slideToggle();
	});		

	/*** Responsive Menu  ***/
	$(".responsive-menu-dropdown").click( function(){
	$(".responsive-menu > ul").slideToggle();
	});
	/*** Responsive Menu  ***/
	$(".right-bar-btn-mobile").click( function(){
	$(".right-bar").slideToggle('slow');
	});

	
	/*** Visitor Widget Tab ***/	
	$(".tabs-menu a").click(function(event) {
	event.preventDefault();
	$(this).parent().addClass("current");
	$(this).parent().siblings().removeClass("current");
	var tab = $(this).attr("href");
	$(".tab-content").not(tab).css("display", "none");
	$(tab).fadeIn();
	});
	
	/*** Gallery Delete Function  ***/
	$(".hide-btn").click( function(){
	$(this).parent().parent().parent().parent().parent().fadeOut();
	});		

function Timeline(cvs) {

    var self = this,
        paused = true,
        rafid = 0,
        mouse = { x: 0, y: 0 },
        canvas = cvs,
        ctx = null;

    self.lines = [];

    self.isOK = false;
    self.options = {
        speed: 0.1,
        density: 8,
        radius: 600,
    };
    self.targets = [
        [29, 32, 48, 68],
        [29, 33, 38]
    ];
    self.dotColors = [
        ['#13669b', 'rgba(19, 102, 155, 0.3)', 'rgba(19, 102, 155, 0.08)'],
        ['#7dd317', 'rgba(113, 222, 15, 0.3)', 'rgba(91, 164, 22, 0.12)'],
    ];

    self.isPaused = function () {
        return paused;
    };

    function InitDots() {
        var tl = $('.timeline2');
        var top = tl.find('h2').outerHeight();

        self.lines[0].dots = [];
        var y = top;
        tl.find('article:first figure').each(function () {

            self.lines[0].dots.push([$(this).outerWidth() + 20, y + 20]);

            y += $(this).outerHeight();
        });

        self.lines[1].dots = [];
        var y = top;
        tl.find('article:last figure').each(function () {

            self.lines[1].dots.push([canvas.width - $(this).outerWidth() - 20, y + 20]);

            y += $(this).outerHeight();
        });
    }

    function OnResize() {
        canvas.width = canvas.offsetWidth;
        canvas.height = canvas.offsetHeight;

        var wasPaused = paused;
        self.toggle(false);
        // Init lines
        self.lines[0].reset(canvas.offsetWidth / 2 - 15);
        self.lines[1].reset(canvas.offsetWidth / 2 + 15);

        InitDots();

        self.toggle(!wasPaused);
    }

    function init() {
        var result = false;
        try {
            result = !!(canvas.getContext && (ctx = canvas.getContext('2d')));

            self.lines[0] = new Line(0, canvas.offsetHeight - 100, '#4789a3', self.options, mouse);
            self.lines[1] = new Line(0, canvas.offsetHeight - 100, '#a0d59c', self.options, mouse);

        } catch (e) {
            return false;
        }

        $(canvas).mousemove(function (e) {

            if (e.offsetX) {
                mouse.x = e.offsetX;
                mouse.y = e.offsetY;
            }
            else if (e.layerX) {
                mouse.x = e.layerX;
                mouse.y = e.layerY;
            }
            else {
                mouse.x = e.pageX - $(canvas).offset().left;
                mouse.y = e.pageY - $(canvas).offset().top;
            }
        });

        $(window).resize(OnResize);

        OnResize();

        return result;
    }

    function Line(y, height, color, options, mouse) {
        var self = this;

        self.color = color;
        self.options = options;
        self.mouse = mouse;
        self.height = height;
        self.dots = [];
        self.y = y;

        self.points = [];

        self.reset = function (x, f) {
            self.points = [];
            for (var y = self.y; y < self.height; y += self.options.density)
                self.points.push(new Point(x, y, self.color));
        }

        self.update = function () {
            for (var i = 0; i < self.points.length; i++)
                self.points[i].update(self.mouse, self.options);
        }

        function Point(x, y) {
            this.y = y;
            this.x = x;
            this.base = { x: x, y: y };

            this.update = function (mouse, options) {
                var dx = this.x - mouse.x,
                    dy = this.y - mouse.y,
                    alpha = Math.atan2(dx, dy),
                    alpha = (alpha > 0 ? alpha : 2 * Math.PI + alpha),
                    d = options.radius / Math.sqrt(dx * dx + dy * dy);

                this.y += Math.cos(alpha) * d + (this.base.y - this.y) * options.speed;
                this.x += Math.sin(alpha) * d + (this.base.x - this.x) * options.speed;
            }
        }
    }

    function drawCircle(p, r, color) {
        ctx.fillStyle = color;
        ctx.beginPath();
        ctx.arc(p.x, p.y, r, 0, 2 * Math.PI, true);
        ctx.closePath();
        ctx.fill();
    }

    function drawLine(p1, p2) {
        ctx.beginPath();
        ctx.moveTo(p1.x, p1.y);
        ctx.lineTo(p2.x, p2.y);
        ctx.stroke();
        ctx.closePath();
    }

    function redraw() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);

        for (var i = 0; i < 2; i++) {
            var points = self.lines[i].points;

            ctx.beginPath();
            ctx.lineWidth = 2;
            ctx.strokeStyle = self.lines[i].color;
            ctx.moveTo(points[15].x, points[15].y);

            for (var j = 15; j < points.length - 2; j++) {
                var point = points[j];

                var xc = (points[j + 1].x + point.x) / 2;
                var yc = (points[j + 1].y + point.y) / 2;


                ctx.quadraticCurveTo(point.x, point.y, xc, yc);
            }
            ctx.stroke();
            ctx.closePath();


            // Dots
            ctx.lineWidth = 1.2;
            ctx.strokeStyle = self.dotColors[i][2];
            for (var j = 0; j < self.lines[i].dots.length; j++) {
                var dot = self.lines[i].dots[j],
                    id = self.targets[i][j];
                    dot2 = [
                        (self.lines[i].points[id].x + self.lines[i].points[id + 1].x) / 2,
                        (self.lines[i].points[id].y + self.lines[i].points[id + 1].y) / 2,
                    ];

                var p1 = { x: dot[0], y: dot[1] };
                var p2 = { x: dot2[0], y: dot2[1] };


                drawLine(p1, p2);
                drawCircle(p1, 3, self.dotColors[i][0]);

                drawCircle(p2, 11, self.dotColors[i][1]);
                drawCircle(p2, 5.5, self.dotColors[i][0]);
            }
        }
    }

    function animate() {
        rafid = requestAnimationFrame(animate);

        self.lines[0].update();
        self.lines[1].update();

        redraw();
    }

    self.toggle = function (run) {
        if (!self.isOK) return false;

        if (run === undefined)
            self.toggle(!paused);

        else if (!!run && paused) {
            paused = false;
            animate();
        }
        else if (!!!run) {
            paused = true;
            cancelAnimationFrame(rafid);
        }
        return true;
    }


    self.isOK = init();
}
new Timeline($('#cvs3').get(0)).toggle(true);

	
});

$(document).ready(function(){ 

	/*** Scrollbar Timeline ***/	
	$('#scrollbox3').enscroll({
	showOnHover: true,
	verticalTrackClass: 'track3',
	verticalHandleClass: 'handle3'
	});	

	/*** Sidebar Scroll ***/	
	$('#scrollbox4').enscroll({
	showOnHover: true,
	verticalTrackClass: 'track3',
	verticalHandleClass: 'handle3'
	});	
	
	/*** Contact list Scroll ***/	
	$('#scrollbox5').enscroll({
	showOnHover: true,
	verticalTrackClass: 'track3',
	verticalHandleClass: 'handle3'
	});	
	
	/*** Chat Widget Scroll ***/	
	$('#scrollbox6').enscroll({
	showOnHover: true,
	verticalTrackClass: 'track3',
	verticalHandleClass: 'handle3'
	});	
	
	/*** Inbox Widget Scroll ***/	
	$('#scrollbox7').enscroll({
	showOnHover: true,
	verticalTrackClass: 'track3',
	verticalHandleClass: 'handle3'
	});	
	
	/*** Inbox Page  Scroll ***/	
	$('#scrollbox8').enscroll({
	showOnHover: true,
	verticalTrackClass: 'track3',
	verticalHandleClass: 'handle3'
	});	
	
	/*** Read Message Scroll ***/	
	$('#scrollbox9').enscroll({
	showOnHover: true,
	verticalTrackClass: 'track3',
	verticalHandleClass: 'handle3'
	});	
	
	
	/*** Carousal Widget ***/
	$('.slidewrap').carousel({
	slider: '.slider',
	slide: '.slide1',
	slideHed: '.slidehed',
	nextSlide : '.next',
	prevSlide : '.prev',
	addPagination: false,
	addNav : false
	});

	/** Profile Tab **/
	$('#tab-content div').hide();
	$('#tab-content div:first').show();

	$('#nav1 li').click(function() {
	$('#nav1 li a').removeClass("active");
	$(this).find('a').addClass("active");
	$('#tab-content div').hide();

	var indexer = $(this).index(); //gets the current index of (this) which is #nav1 li
	$('#tab-content div:eq(' + indexer + ')').fadeIn(); //uses whatever index the link has to open the corresponding box 
	});
});	

/*** Sortable Table  ***/
		$(document).ready(function(){

		$(".sortable-table th").click(function(){
		sort_table($(this));
		});

		});

		function sort_table(clicked){
		var current_table = clicked.parents(".sortable-table"),
		sorted_column = clicked.index(),
		column_count = current_table.find("th").length,
		sort_it = [];

		current_table.find("tbody tr").each(function(){
		var new_line = "",
		sort_by = "";
		$(this).find("td").each(function(){
		if($(this).next().length){
		new_line += $(this).html() + "+";
		}else{
		new_line += $(this).html();
		}
		if($(this).index() == sorted_column){
		sort_by = $(this).text(); 
		}
		});

		new_line = sort_by + "*" + new_line;
		sort_it.push(new_line);
		//console.log(sort_it);

		});

		sort_it.sort();
		$("th span").text("");
		if(!clicked.hasClass("sort-down")){
		clicked.addClass("sort-down");
		clicked.find("span").text("▼");
		}else{
		sort_it.reverse();
		clicked.removeClass("sort-down");
		clicked.find("span").text("▲");
		}

		$("#country-list tr:not('.country-table-head')").each(function(){
		$(this).remove();
		});

		$(sort_it).each(function(index, value) {
		$('<tr class="current-tr"></tr>').appendTo(clicked.parents("table").find("tbody"));
		var split_line = value.split("*"),
		td_line = split_line[1].split("+"),
		td_add = "";

		//console.log(td_line.length);
		for (var i = 0; i < td_line.length; i++){
		td_add += "<td>" + td_line[i] + "</td>";
		}
		$(td_add).appendTo(".current-tr");
		$(".current-tr").removeClass("current-tr");

});
}

		
		
		
$(document).ready(function() {
  $('#reportrange').daterangepicker(
	 {
		startDate: moment().subtract('days', 29),
		endDate: moment(),
		minDate: '01/01/2012',
		maxDate: '12/31/2014',
		dateLimit: { days: 60 },
		showDropdowns: true,
		showWeekNumbers: true,
		timePicker: false,
		timePickerIncrement: 1,
		timePicker12Hour: true,
		ranges: {
		   'Today': [moment(), moment()],
		   'Yesterday': [moment().subtract('days', 1), moment().subtract('days', 1)],
		   'Last 7 Days': [moment().subtract('days', 6), moment()],
		   'Last 30 Days': [moment().subtract('days', 29), moment()],
		   'This Month': [moment().startOf('month'), moment().endOf('month')],
		   'Last Month': [moment().subtract('month', 1).startOf('month'), moment().subtract('month', 1).endOf('month')]
		},
		opens: 'left',
		buttonClasses: ['btn btn-default'],
		applyClass: 'btn-small btn-primary',
		cancelClass: 'btn-small',
		format: 'MM/DD/YYYY',
		separator: ' to ',
		locale: {
			applyLabel: 'Submit',
			fromLabel: 'From',
			toLabel: 'To',
			customRangeLabel: 'Custom Range',
			daysOfWeek: ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr','Sa'],
			monthNames: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
			firstDay: 1
		}
	 },
	 function(start, end) {
	  console.log("Callback has been called!");
	  $('#reportrange span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
	 }
  );
  //Set the initial state of the picker label
  $('#reportrange span').html(moment().subtract('days', 29).format('MMMM D, YYYY') + ' - ' + moment().format('MMMM D, YYYY'));
});


