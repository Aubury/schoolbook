/* DB Snow Flakes Script */

(function( $ ) {

    let dbSnowFlakesMaxNumber
    let dbSnowFlakesMinSize
    let dbSnowFlakesMaxSize
    let dbSnowFlakesSpeed
    let dbSnowFlakesOpacity
    
    let dbSnow = new Array()
    let dbMarginBottom
    let dbMarginRight
    let coord = new Array()
    let sway = new Array()
    let moveX = new Array()
    let timer

    $(document).ready(function(){

        let dbScreenWidth = 0;
        let dbScreenWidthNew = dbSnowFlakesScreen();
        dbMarginBottom = $( window ).height() + 50;
        dbMarginRight = $( window ).width() - 15;
        dbSnowFlakesSettings( dbScreenWidth, dbScreenWidthNew );

        $(window).on( 'resize', function() {
      
            dbScreenWidthNew = dbSnowFlakesScreen();
            dbMarginBottom = $( window ).height() + 50;
            dbMarginRight = $( window ).width() - 15;
            dbSnowFlakesSettings( dbScreenWidth, dbScreenWidthNew );
            dbScreenWidth = dbScreenWidthNew;

        });

    });

    function dbSnowFlakesScreen() {

        let size = $(window).width();
        let screen;

        if ( size <= 576 ) {
            screen = 'mobile';
        }
        else if ( size <= 992 ) {
            screen = 'tablet';
        }
        else {
            screen = 'desktop';
        }
        
        return screen;

    }

    function dbSnowFlakesSettings( screen_before, screen_now ) {

        $( '[id*=db_snowflake_]' ).remove();
        dbSnowAction( false );

        if ( screen_before !== screen_now ) {

            let screen = screen_now;

            switch ( screen ) {

                case 'mobile':

                    dbSnowFlakesMaxNumber = dbSnowFlakesMaxNumberMobile;
                    dbSnowFlakesMinSize = dbSnowFlakesMinSizeMobile;
                    dbSnowFlakesMaxSize = dbSnowFlakesMaxSizeMobile;
                    dbSnowFlakesSpeed = dbSnowFlakesSpeedMobile;
                    dbSnowFlakesOpacity = dbSnowFlakesOpacityMobile;
                    break;

                case 'tablet':

                    dbSnowFlakesMaxNumber = dbSnowFlakesMaxNumberTablet;
                    dbSnowFlakesMinSize = dbSnowFlakesMinSizeTablet;
                    dbSnowFlakesMaxSize = dbSnowFlakesMaxSizeTablet;
                    dbSnowFlakesSpeed = dbSnowFlakesSpeedTablet;
                    dbSnowFlakesOpacity = dbSnowFlakesOpacityTablet;
                    break;

                default:

                    dbSnowFlakesMaxNumber = dbSnowFlakesMaxNumberDesktop;
                    dbSnowFlakesMinSize = dbSnowFlakesMinSizeDesktop;
                    dbSnowFlakesMaxSize = dbSnowFlakesMaxSizeDesktop;
                    dbSnowFlakesSpeed = dbSnowFlakesSpeedDesktop;
                    dbSnowFlakesOpacity = dbSnowFlakesOpacityDesktop;
                    break;

            }

        }

        dbSnowMakeSnowFlakes( dbSnowFlakesMaxNumber, dbSnowFlakesMaxSize );

    }

    function dbSnowMakeSnowFlakes( max, margin ) {

        let dbSnowSymbol = "&#10052;"

        for ( let i = 0; i < max; i++ )
            {
                $( 'body' ).append(
                    "<span id='db_snowflake_" + i + "' style='user-select:none;position:fixed;top:-" + margin + "px'>" + dbSnowSymbol + "</span>"
                );
            }

            dbSnowInit();

    }

    function dbGetRandom( min, max ) {
        return Math.floor ( Math.random() * ( max - min ) ) + min;
    }

    function dbSnowInit()
    {
        let dbSnowFont = "Times";

        for ( i = 0; i < dbSnowFlakesMaxNumber; i++ )
        {
            dbSnow[i] = document.getElementById("db_snowflake_" + i);
            dbSnow[i].style.fontFamily = dbSnowFont;
            dbSnow[i].size = dbGetRandom( dbSnowFlakesMaxSize, dbSnowFlakesMinSize );
            dbSnow[i].style.fontSize = dbSnow[i].size + 'px';
            dbSnow[i].style.color = dbSnowFlakesColors[ dbGetRandom( dbSnowFlakesColors.length, 1 ) ];
            dbSnow[i].style.textShadow = dbSnowFlakesColors[ dbGetRandom( dbSnowFlakesColors.length, 1 ) ];
            dbSnow[i].style.zIndex = 99999;
            dbSnow[i].speed = dbSnowFlakesSpeed * dbSnow[i].size / 5;
            dbSnow[i].posx = dbGetRandom( dbMarginRight, dbSnow[i].size );
            dbSnow[i].posy = dbGetRandom( dbMarginBottom, 2 * dbSnow[i].size );
            dbSnow[i].style.left = dbSnow[i].posx + 'px';
            dbSnow[i].style.top = dbSnow[i].posy + 'px';
            dbSnow[i].style.opacity = dbSnowFlakesOpacity;
    
            coord[i] = 0;
            sway[i] = Math.random() * 15;
            moveX[i] = 0.03 + Math.random() / 10;
        }

        dbSnowAction( true )
    }

    function dbSnowAction( repeat )
    {
        if ( repeat === true ) {

            for ( i = 0; i < dbSnowFlakesMaxNumber; i++ )
            {
                coord[i] += moveX[i];
                dbSnow[i].posy += dbSnow[i].speed;
                dbSnow[i].style.top = dbSnow[i].posy + 'px';
                dbSnow[i].style.left = dbSnow[i].posx + sway[i] * Math.sin( coord[i] ) + 'px';
                dbSnow[i].style.top = dbSnow[i].posy + 'px';

                if ( dbSnow[i].posy >= dbMarginBottom - 2 * dbSnow[i].size )
                    dbSnow[i].posy = 0;
                
                if ( parseInt( dbSnow[i].style.left ) > ( dbMarginRight - 3 * sway[i] ) )
                    dbSnow[i].posx = dbGetRandom( dbMarginRight , dbSnow[i].size );
            }

            timer = setTimeout( dbSnowAction, 50, true );

        }
        else {
            
            clearTimeout( timer );
            return;

        }
    }

})(jQuery);