
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

window.Vue = require('vue');

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

Vue.component('example', require('./components/Example.vue'));

Vue.component(
    'passport-clients',
    require('./components/passport/Clients.vue')
);

Vue.component(
    'passport-authorized-clients',
    require('./components/passport/AuthorizedClients.vue')
);

Vue.component(
    'passport-personal-access-tokens',
    require('./components/passport/PersonalAccessTokens.vue')
);

Vue.component('usermovies', require('./components/UserMovies.vue'));

//!!!!!add components above this next line!!!!!
const app = new Vue({
    el: '#app'
});


// Vue.component('user-movies', require('./components/UserMovies.vue'));


// Vue.http.interceptors.push((request, next) => {
//     request.headers.set('X-CSRF-TOKEN', Laravel.csrfToken);

//     next();
// });


$('#loginForm').on('submit', function (e)
    {
        e.preventDefault();
        console.log("form submitted");

        $.post($(this).attr('action'), 
                {
                    email: $('#email').val(), 
                    password: $('#password').val()    
                }, 
                function(response)
                    {
                        console.log(response);

                        if(response.success)
                        {
                            location.href = "movies";
                            // $.ajax(
                            //     {
                                    
                            //         url: 'movies',
                            //         type: 'get',
                            //         headers: {'X-CSRF-Token': $("meta[name='csrf-token']").attr('content'),
                            //                     'Authorization': response.success.token
                            //                 }//,
                            //         // success: function(response)
                            //         // {
                            //         //     console.log(response);
                            //         // }
                            //     }
                            // )
                        }



                    }
                )
    });

