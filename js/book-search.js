    /**
     * Libraray Book Search Plugin Scripts
     */
    jQuery( document ).ready(function($) {
        /**
         * Submit form data using ajax
         */
        $("#book-search-form").submit(function( event ) { 
            event.preventDefault();
            let bookSearchPrice = $(".priceRange").val();
            let bookSearchTitle = $("#book-search-title").val();
            let bookSearchAuthor = $("#book-search-author").val();
            let bookSearchPublisher = $("#book-search-publisher").val();
            let bookSearchRating = $("#book-search-rating").val();
            if(bookSearchTitle == "" && bookSearchAuthor == "" && bookSearchPublisher == "" && bookSearchRating == ""){
                $('#book-search-results').html("<h6 style='text-align:center'>Please fill the search details</h6>");
                return false;
            }
            $.ajax({ 
                type:"POST",
                url:bookSearch.ajaxUrl,
                data: {
                    'action':'bookSearch',
                    'searchString': $( this ).serialize() + '&book_search_price=' + bookSearchPrice,
                    'security' : bookSearch.ajax_nonce,   
                },
                success:function(response){
                    $('#book-search-results').html(response);
                }
            });
        });
        /**
         * Add Price range functionality 
         */
        $("#price-range").slider({
            step: 1,
            range: true, 
            min: 1, 
            max: 3000, 
            values: [1, 3000], 
            slide: function(event, ui)
            {$(".priceRange").val(ui.values[0] + " - " + ui.values[1]);}
          });
        $(".priceRange").val($("#price-range").slider("values", 0) + " - " + $("#price-range").slider("values", 1));
        /**
         * Pagination functionality using ajax 
         */
        $(document.body).on("click", "#pagination a", function( event ) { 
            event.preventDefault();
            let bookSearchPrice = $(".priceRange").val();
            let bookSearchTitle = $("#book-search-title").val();
            let bookSearchAuthor = $("#book-search-author").val();
            let bookSearchPublisher = $("#book-search-publisher").val();
            let bookSearchRating = $("#book-search-rating").val();
            let page = getQueryString($(this).attr('href'));
            $.ajax({ 
                type:"POST",
                url:bookSearch.ajaxUrl,
                data: {
                    'action':'bookSearch',
                    'page': page,
                    'searchString': 'book_search_title=' + bookSearchTitle + '&book_search_author=' + 
                     bookSearchAuthor + '&book_search_publisher=' + bookSearchPublisher + '&book_search_rating=' + 
                     bookSearchRating + '&book_search_price=' + bookSearchPrice + '&book_search_offset=2',
                    'security' : bookSearch.ajax_nonce,      
                },
                success:function(response){
                    $('#book-search-results').html(response);
                }
            });
        });
        /**
         * 
         * @param {*} str 
         * @returns pagination page number
         */
        function getQueryString(str) {
            return str.split('?paged=')[1];
        }
    });