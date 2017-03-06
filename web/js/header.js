$(document).ready(function()
{
    function closeNav(e)
    {
        var tarClass = e.target.className;
        if(tarClass.indexOf('nav')>= 0) return;
        $('.user').removeClass('sel');
        $('.nav').removeClass('nav_show');
        $(document).unbind('click', closeNav);
    }
    $('.user').on('click',function(e)
    {
        var that = $(this);
        if(that.hasClass('sel')) return;
        
        that.addClass('sel');
        
        $('.nav').addClass('nav_show');
        
        e.stopPropagation();
        e.stopImmediatePropagation();
        e.cancelBubble = true;
        
        $(document).bind('click', closeNav);
        
    });
});