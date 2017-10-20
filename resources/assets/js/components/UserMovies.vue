<style scoped>
    .action-link {
        cursor: pointer;
    }

    .m-b-none {
        margin-bottom: 0;
    }
  

    th.active {
    color: #000;
    }

    th.active .arrow {
    opacity: 1;
    }

    .arrow {
    display: inline-block;
    vertical-align: middle;
    width: 0;
    height: 0;
    margin-left: 5px;
    opacity: 0.66;
    }

    .arrow.asc {
    border-left: 4px solid transparent;
    border-right: 4px solid transparent;
    border-bottom: 4px solid #000;
    }

    .arrow.desc {
    border-left: 4px solid transparent;
    border-right: 4px solid transparent;
    border-top: 4px solid #000;
    } 

    .arrow.none {
    border-left: 0px solid transparent;
    border-right: 0px solid transparent;
    border-top: 0px solid #000;
    } 

</style>

<template>
    <div>
        <div class="panel panel-default">
            <div class="panel-heading">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span>
                        Your Movies
                    </span>

                    <a class="action-link" @click="showCreateMovieForm">
                        Add Movie
                    </a>
                </div>
            </div>

            <div class="panel-body">
                <!-- Current userMovies -->
                <p class="m-b-none" v-if="userMovies.length === 0">
                    You have not added any movies.
                </p>

                <table class="table table-borderless m-b-none" v-if="userMovies.length > 0">
                    <thead>
                        <tr>
                            <th v-show=false>Id</th>
                            <th @click="getSortedUserMovies('title')" :class="{ active: sortKey == 'title' }">Title <span class="arrow" :class="sortOrders['title'] > 0 ? 'asc' : sortOrders['title'] < 0 ? 'desc' : 'none'"></span></th>
                            <th @click="getSortedUserMovies('name')" :class="{ active: sortKey == 'name' }" class="toggle-header2">Format<span class="arrow" :class="sortOrders['name'] > 0 ? 'asc' : sortOrders['name'] < 0 ? 'desc' : 'none'"></span></th>
                            <th @click="getSortedUserMovies('lengthTotal')" :class="{ active: sortKey == 'lengthTotal' }"class="toggle-header">Length<span class="arrow" :class="sortOrders['lengthTotal'] > 0 ? 'asc' : sortOrders['lengthTotal'] < 0 ? 'desc' : 'none'"></span></th>
                            <th @click="getSortedUserMovies('releaseYear')" :class="{ active: sortKey == 'releaseYear' }" class="toggle-header2">Year<span class="arrow" :class="sortOrders['releaseYear'] > 0 ? 'asc' : sortOrders['releaseYear'] < 0 ? 'desc' : 'none'"></span></th>
                            <th @click="getSortedUserMovies('rating')" :class="{ active: sortKey == 'rating' }" class="toggle-header">Rating<span class="arrow" :class="sortOrders['rating'] > 0 ? 'asc' : sortOrders['rating'] < 0 ? 'desc' : 'none'"></span></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="userMovie in userMovies"  >
                            <!-- ID -->
                            
                            <td v-show=false style="vertical-align: middle;">
                                {{ userMovie.id }}                                
                            </td>

                            <!-- title -->
                            <td style="vertical-align: middle;" >
                                {{ userMovie.title }}
                            </td>

                            <!-- format -->
                            <td style="vertical-align: middle;" class="toggle-column2">
                                {{formats[formats.map(item => item.id).indexOf(userMovie.formatId)].name}}
                               
                            </td>

                            <!-- length -->
                            <td style="vertical-align: middle;" class="toggle-column">
                                {{ userMovie.lengthHour }} hr {{ userMovie.lengthMinute }} m
                            </td>
                            <!-- Year -->
                            <td style="vertical-align: middle;" class="toggle-column2">
                                {{ userMovie.releaseYear }}
                            </td>
                            <!-- Rating -->
                            <td style="vertical-align: middle;" class="toggle-column">
                                {{ userMovie.rating }}
                            </td>

                            <!-- Edit Button -->
                            <td style="vertical-align: middle;">
                                <a class="action-link" @click="edit(userMovie)">                                    
                                    <img  src="/images/pencil-32x32.png" alt="Edit" title="Edit" >
                                </a>
                            </td>

                            <!-- Delete Button -->
                            <td style="vertical-align: middle;">
                                <a class="action-link text-danger" @click="destroy(userMovie)">
                                    <img  src="/images/deletered-32x32.png" alt="Delete" title="Delete">
                                </a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Create Movie Modal -->
        <div class="modal fade" id="modal-create-movie" tabindex="-1" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button " class="close" data-dismiss="modal" aria-hidden="true">&times;</button>

                        <h4 class="modal-title">
                            Add Movie
                        </h4>
                    </div>

                    <div class="modal-body">
                        <!-- Form Errors -->
                        <div class="alert alert-danger" v-if="createForm.errors.length > 0">
                            <p><strong>Whoops!</strong> Something went wrong!</p>
                            <br>
                            <ul>
                                <li v-for="error in createForm.errors">
                                    {{ error }}
                                </li>
                            </ul>
                        </div>

                        <!-- Create Movie Form -->
                        <form class="form-horizontal" role="form">
                            <!-- Title -->
                            <div class="form-group" v-bind:class="{ 'has-error': submition && incorrectCreateTitle }">                            
                                <label class="col-md-3 control-label">Title</label>

                                <div class="col-md-7">
                                    <input id="create-movie-title" type="text" class="form-control"
                                                                @keyup.enter="store" v-model="createForm.title">

                                    <span class="help-block">
                                        Full title of movie.
                                    </span>
                                    <div class="help-block" v-if="submition && incorrectCreateTitle">This field is required and max length is 50.</div>
                                </div>
                            </div>
                            
                            <!-- Format -->
                            <div class="form-group" v-bind:class="{ 'has-error': submition && incorrectCreateFormat }">
                                <label class="col-md-3 control-label">Format</label>

                                <div class="col-md-7">
                                    <select id="create-movie-formatId" v-model="createForm.formatId">
                                        <option v-for="format in formats" :value="format.id">
                                            {{format.name}}
                                        </option>
                                    </select>

                                    <span class="help-block">
                                        Format of your movie.
                                    </span>
                                    <div class="help-block" v-if="submition && incorrectCreateFormat">This field is required.</div>
                                </div>
                            </div>
                            
                            <!-- Length -->
                            <div class="form-group" v-bind:class="{ 'has-error': submition && incorrecCreatetLength }">
                                <label class="col-md-3 control-label">Length</label>

                                <div class="col-md-7">
                                    <input type="text" class="form-control-small" name="lengthHour" @keyup.enter="store" v-model="createForm.lengthHour"> hr  
                                    <input type="text" class="form-control-small" name="lengthMinute" @keyup.enter="store" v-model="createForm.lengthMinute"> m 
                                    <span class="help-block">
                                        The length of the movie.
                                    </span>
                                    <div class="help-block" v-if="submition && incorrecCreatetLength">These fields combined must be between 1 and 500 minutes.</div>
                                </div>
                            </div>

                            <!-- Year -->
                            <div class="form-group" v-bind:class="{ 'has-error': submition && incorrecCreatetYear }">
                                <label class="col-md-3 control-label">Year</label>

                                <div class="col-md-7">
                                    <input type="text" class="form-control-small" name="year"
                                                    @keyup.enter="store" v-model="createForm.year">

                                    <span class="help-block">
                                        Year movie was released.
                                    </span>
                                    <div class="help-block" v-if="submition && incorrecCreatetYear">This field must be betwee 1801 and 2099.</div>
                                </div>
                            </div>

                            <!-- Rating -->
                            <div class="form-group">
                                <label class="col-md-3 control-label">Rating</label>

                                <div class="col-md-7">
                                    <span>
                                        <input type="radio" id="rating" value="1" label="1" class="radio-button" v-model="createForm.rating" v-bind:value="1">
                                    </span>
                                    <span>
                                        <input type="radio" id="rating" value="2" label="2" class="radio-button" v-model="createForm.rating" v-bind:value="2">
                                    </span>
                                    <span>
                                        <input type="radio" id="rating" value="3" label="3" class="radio-button" v-model="createForm.rating" v-bind:value="3">
                                    </span>
                                    <span>
                                        <input type="radio" id="rating" value="4" label="4" class="radio-button" v-model="createForm.rating" v-bind:value="4">
                                    </span>
                                    <span>
                                        <input type="radio" id="rating" value="5" label="5" class="radio-button" v-model="createForm.rating" v-bind:value="5">
                                    </span>                                    
                                   
                                    <span class="help-block">
                                        Your rating - 1=Terrible to 5=Excellent.
                                    </span>
                                </div>
                            </div>

                        </form>
                    </div>

                    <!-- Modal Actions -->
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>

                        <button type="button" class="btn btn-primary" @click="store">
                            Add
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit movie Modal -->
        <div class="modal fade" id="modal-edit-movie" tabindex="-1" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button " class="close" data-dismiss="modal" aria-hidden="true">&times;</button>

                        <h4 class="modal-title">
                            Edit Movie
                        </h4>
                    </div>

                    <div class="modal-body">
                        <!-- Form Errors -->
                        <div class="alert alert-danger" v-if="editForm.errors.length > 0">
                            <p><strong>Whoops!</strong> Something went wrong!</p>
                            <br>
                            <ul>
                                <li v-for="error in editForm.errors">
                                    {{ error }}
                                </li>
                            </ul>
                        </div>

                        <!-- Edit Movie Form -->
                        <form class="form-horizontal" role="form">                            
                            <!-- Title -->
                            <div class="form-group" v-bind:class="{ 'has-error': submition && incorrectEditTitle }">
                                <label class="col-md-3 control-label">Name</label>

                                <div class="col-md-7">
                                    <input id="edit-movie-title" type="text" class="form-control"
                                                                @keyup.enter="update" v-model="editForm.title">

                                    <span class="help-block">
                                        Full title of movie.
                                    </span>
                                    <div class="help-block" v-if="submition && incorrectEditTitle">This field is required and max length is 50.</div>
                                </div>
                            </div>

                            <!-- Format -->
                            <div class="form-group" v-bind:class="{ 'has-error': submition && incorrectEditFormat }">
                                <label class="col-md-3 control-label">Format</label>

                                <div class="col-md-7">
                                    <select id="edit-movie-formatId" v-model="editForm.formatId">
                                        <option v-for="format in formats" :value="format.id">
                                            {{format.name}}
                                        </option>
                                    </select>

                                    <span class="help-block">
                                        Format of your movie.
                                    </span>
                                    <div class="help-block" v-if="submition && incorrectEditFormat">This field is required.</div>
                                </div>
                            </div>

                            <!-- Length -->
                            <div class="form-group" v-bind:class="{ 'has-error': submition && incorrecEditLength }">
                                <label class="col-md-3 control-label">Length</label>

                                <div class="col-md-7">                                    
                                    <input type="text" class="form-control-small" name="lengthHour" @keyup.enter="store" v-model="editForm.lengthHour"> hr  
                                    <input type="text" class="form-control-small" name="lengthMinute" @keyup.enter="store" v-model="editForm.lengthMinute"> m

                                    <span class="help-block">
                                        The length of the movie.
                                    </span>
                                    <div class="help-block" v-if="submition && incorrecEditLength">These fields combined must be between 1 and 500 minutes.</div>
                                </div>
                            </div>

                            <!-- Year -->
                            <div class="form-group" v-bind:class="{ 'has-error': submition && incorrecEditYear }">
                                <label class="col-md-3 control-label">Year</label>

                                <div class="col-md-7">
                                    <input type="text" class="form-control-small" name="year"
                                                    @keyup.enter="store" v-model="editForm.year">

                                    <span class="help-block">
                                        Year movie was released.
                                    </span>
                                    <div class="help-block" v-if="submition && incorrecEditYear">This field must be betwee 1801 and 2099.</div>
                                </div>
                            </div>

                            <!-- Rating -->
                            <div class="form-group">
                                <label class="col-md-3 control-label">Rating</label>

                                <div class="col-md-7">
                                    <span>
                                        <input type="radio" id="edit-rating" value="1" label="1" class="radio-button" v-model="editForm.rating" v-bind:value="1">
                                    </span>
                                    <span>
                                        <input type="radio" id="edit-rating" value="2" label="2" class="radio-button" v-model="editForm.rating" v-bind:value="2">
                                    </span>
                                    <span>
                                        <input type="radio" id="edit-rating" value="3" label="3" class="radio-button" v-model="editForm.rating" v-bind:value="3">
                                    </span>
                                    <span>
                                        <input type="radio" id="edit-rating" value="4" label="4" class="radio-button" v-model="editForm.rating" v-bind:value="4">
                                    </span>
                                    <span>
                                        <input type="radio" id="edit-rating" value="5" label="5" class="radio-button" v-model="editForm.rating" v-bind:value="5">
                                    </span>                                    
                                   
                                    <span class="help-block">
                                        Your rating - 1=Terrible to 5=Excellent.
                                    </span>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Modal Actions -->
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>

                        <button type="button" class="btn btn-primary" @click="update">
                            Save Changes
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        /*
         * The component's data.
         */
        data() {
            return {
                userMovies: [],

                formats: [],

                createForm: {
                    errors: [],
                    title: '', 
                    formatId: '', 
                    lengthTotal: '',
                    lengthHour: '',
                    lengthMinute: '',
                    year: '',
                    rating: '',                    
                },

                editForm: {
                    errors: [],
                    title: '',
                    formatId: '', 
                    lengthTotal: '',
                    lengthHour: '',
                    lengthMinute: '',
                    year: '',
                    rating: '',                    
                }, 

                sortOrders : {},

                sortKey : '',

                submition: false,
            };
        },

        /**
         * Prepare the component (Vue 1.x).
         */
        ready() {
            this.prepareComponent();
        },

        /**
         * Prepare the component (Vue 2.x).
         */
        mounted() {
            this.prepareComponent();
        },

        computed: 
        {
            incorrectCreateTitle() 
            { 
                if(this.createForm.title === '' || this.createForm.title.length > 50)
                {
                    return true;
                } 

                return false;
            },
            incorrectEditTitle() 
            { 
                if(this.editForm.title === '' || this.editForm.title.length > 50)
                {
                    return true;
                } 

                return false;
            },
            incorrectCreateFormat() 
            { 
                return this.createForm.formatId === '' 
            },
            incorrectEditFormat() 
            { 
                return this.editForm.formatId === '' 
            },
            incorrecCreatetLength() 
            { 
                //  time, minutes, >0 and <500)   
                var hour = 0;
                var minute = 0;
                if(this.createForm.lengthHour.toString().trim().length > 0)
                {
                    if(isNaN(this.createForm.lengthHour) )
                    {
                        return true;
                    }
                    hour = parseInt(this.createForm.lengthHour)
                }

                if(this.createForm.lengthMinute.toString().trim().length > 0)
                {
                    if(isNaN(this.createForm.lengthMinute) )
                    {
                        return true;
                    }
                    minute = parseInt(this.createForm.lengthMinute)
                }

                this.createForm.lengthTotal =  hour * 60 + minute;

                //Check to see if lengthTotal is less than 1 or greater than 500 minutes
                if( isNaN(this.createForm.lengthTotal) || this.createForm.lengthTotal < 1 ||  this.createForm.lengthTotal > 500 )
                {
                    return true;
                }               

                return false;
            },
            incorrecEditLength() 
            { 
                //  time, minutes, >0 and <500)   
                var hour = 0;
                var minute = 0;
                if(this.editForm.lengthHour.toString().trim().length > 0)
                {
                    if(isNaN(this.editForm.lengthHour) )
                    {
                        return true;
                    }
                    hour = parseInt(this.editForm.lengthHour)
                }

                if(this.editForm.lengthMinute.toString().trim().length > 0)
                {
                    if(isNaN(this.editForm.lengthMinute) )
                    {
                        return true;
                    }
                    minute = parseInt(this.editForm.lengthMinute)
                }

                this.editForm.lengthTotal =  hour * 60 + minute;

                //Check to see if lengthTotal is less than 1 or greater than 500 minutes
                if( isNaN(this.editForm.lengthTotal) || this.editForm.lengthTotal < 1 ||  this.editForm.lengthTotal > 500 )
                {
                    return true;
                }               

                return false;
            },
            incorrecCreatetYear() 
            { 
                //integer, >1800 and < 2100)                
                if(isNaN(this.createForm.year) || this.createForm.year < 1801  || this.createForm.year > 2099)
                {
                    return true;
                } 

                return false;
            },
            incorrecEditYear() 
            { 
                //integer, >1800 and < 2100)                
                if(isNaN(this.editForm.year) || this.editForm.year < 1801  || this.editForm.year > 2099)
                {
                    return true;
                } 

                return false;
            },
        },
    
        methods: 
        {

            isEmail() 
            {

            },

            validateCreateForm() 
            {
                this.submition = true;

                if(this.incorrectCreateTitle || this.incorrectCreateFormat || this.incorrecCreatetLength || this.incorrecCreatetYear)
                {
                    return false;
                }

                return true;
    
            },

            validateEditForm() 
            {
                this.submition = true;

                if(this.incorrectEditTitle || this.incorrectEditFormat || this.incorrecEditLength || this.incorrecEditYear)
                {
                    return false;
                }

                return true;
    
            },

            /**
             * Prepare the component.
             */
            prepareComponent() {
                this.getUserMovies();

                $('#modal-create-movie').on('shown.bs.modal', () => {
                    $('#create-movie-title').focus();
                });

                $('#modal-edit-movie').on('shown.bs.modal', () => {
                    $('#edit-movie-title').focus();
                });
            },

            /**
             * Get all of the movies for the user.
             */
            getUserMovies() 
            {
                axios.get('/api/get-formats')
                        .then(response => 
                        {
                            this.formats = response.data.success.formats;
                            axios.get('/api/get-user-movies')
                                    .then(response => 
                                    {
                                        this.userMovies = response.data.success.userMovies;                                       

                                        if(this.userMovies[0] !== null)
                                        {
                                            for(var i=0; i<this.userMovies.length; i++)
                                            {
                                                this.userMovies[i]['lengthHour'] = Math.floor(this.userMovies[i]["lengthTotal"]/60);
                                                this.userMovies[i]['lengthMinute'] = this.userMovies[i]["lengthTotal"] % 60;
                                            }

                                            //only need to grab one set
                                            var temp = Object.keys(this.userMovies[0]);
                                            for (i=0; i<temp.length; i++) 
                                            {
                                                this.sortOrders[temp[i]] = 0;
                                            }   
                                        }                                          
                                    });
                        });
            },

            getSortedUserMovies(orderBy) //TODO: look into using just getUserMovies
            {                
                this.sortOrders[orderBy] == 0 ? this.sortOrders[orderBy] = 1 : this.sortOrders[orderBy] > 0 ? this.sortOrders[orderBy] = -1 : this.sortOrders[orderBy] = 0;
                var direction = this.sortOrders[orderBy] > 0 ? 'asc' : this.sortOrders[orderBy] < 0 ? 'desc' : 'none';
                axios.get('/api/get-user-movies?orderBy='+orderBy+"&direction="+direction)
                        .then(response => 
                        {
                            this.userMovies = response.data.success.userMovies;
                           
                            if(this.userMovies[0] !== null)
                            {
                                for(var i=0; i<this.userMovies.length; i++)
                                {
                                    this.userMovies[i]['lengthHour'] = Math.floor(this.userMovies[i]["lengthTotal"]/60);
                                    this.userMovies[i]['lengthMinute'] = this.userMovies[i]["lengthTotal"] % 60;
                                }

                                //only need to grab one set
                                //reset sortOrders array
                                var temp = Object.keys(this.sortOrders);
                                for (var i=0; i<temp.length; i++)                                
                                {
                                    this.sortOrders[temp[i]] = 0        
                                } 
                                
                                this.sortOrders[response.data.success.sorting['orderBy']] = response.data.success.sorting['direction'] == 'asc' ? 1 : response.data.success.sorting['direction'] == 'desc' ? -1 : 0; 

                                this.sortKey = response.data.success.sorting['orderBy'];
                            }  
                            
                        });
                       
            },

            /**
             * Show the form for creating new movie.
             */
            showCreateMovieForm() {
                $('#modal-create-movie').modal('show');
            },

            /**
             * Create a new movie for the user.
             */
            store() 
            {
                //this.createForm.lengthTotal =  parseInt(this.createForm.lengthHour) * 60 +  parseInt(this.createForm.lengthMinute);
                this.createForm.year = parseInt(this.createForm.year);
                if(this.validateCreateForm())
                {
                    this.persistMovie(
                        'post', 'api/create-movie',
                        this.createForm, '#modal-create-movie'
                    );
                }
            },




            /**
             * Edit the given movie.
             */
            edit(userMovie) {
                this.editForm.movieId = userMovie.id;
                this.editForm.title = userMovie.title;
                this.editForm.formatId = userMovie.formatId;
                this.editForm.lengthHour = userMovie.lengthHour;
                this.editForm.lengthMinute = userMovie.lengthMinute;
                this.editForm.year = userMovie.releaseYear;
                this.editForm.rating = userMovie.rating;
                this.editForm.lengthTotal = '';

                $('#modal-edit-movie').modal('show');
            },

            /**
             * Update the movie being edited.
             */
            update() 
            {
                //this.editForm.lengthTotal = parseInt(this.editForm.lengthHour) * 60 + parseInt(this.editForm.lengthMinute);
                this.editForm.year = parseInt(this.editForm.year);

                if(this.validateEditForm())
                {
                    this.persistMovie(
                        'patch', '/api/update-movie/' + this.editForm.movieId,
                        this.editForm, '#modal-edit-movie'
                    );
                }
            },

            /**
             * Persist the movie to storage using the given form.
             */
            persistMovie(method, uri, form, modal) {
                form.errors = [];

                axios[method](uri, form)
                    .then(response => 
                    {
                        this.submition = false;

                        this.getUserMovies();

                        form.title = '';
                        form.formatId = '';
                        form.lengthHour = '';
                        form.lengthMinute = '';
                        form.year = '';
                        form.rating = '';
                        form.errors = [];
                        form.lengthTotal = '';

                        $(modal).modal('hide');
                    })
                    .catch(error => {
                        if (typeof error.response.data === 'object') {
                            form.errors = _.flatten(_.toArray(error.response.data));
                        } else {
                            form.errors = ['Something went wrong. Please try again.'];
                        }
                    });
            
            },

            /**
             * Destroy the given movie.
             */
            destroy(userMovie) 
            {
                if (confirm("Are you sure you want to delete this movie?") == true) 
                {
                    axios.delete('/api/delete-movie/' + userMovie.id) //userMovie.id is movieId
                            .then(response => {
                                this.getUserMovies();
                            });
                }
            }
        }
    }
</script>
