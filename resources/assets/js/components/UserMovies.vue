<style scoped>
    .action-link {
        cursor: pointer;
    }

    .m-b-none {
        margin-bottom: 0;
    }

    @media (max-width: 550px)
    {
        .toggle-header
        {
            display:none;
        }
        .toggle-column
        {
            display:none;
        }
    }  

    @media (max-width: 375px)
    {
        .toggle-header2
        {
            display:none;
        }
        .toggle-column2
        {
            display:none;
        }
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

.arrow.dsc {
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
                            <th @click="getSortedUserMovies('title')"       :class="{ active: sortKey == 'title' }">                                Title <span class="arrow"   :class="sortOrders['title'] > 0 ? 'asc' : sortOrders['title'] < 0 ? 'des' : 'none'"></span></th>
                            <th @click="getSortedUserMovies('name')"        :class="{ active: sortKey == 'name' }"          class="toggle-header2"> Format <span class="arrow"  :class="sortOrders['name'] > 0 ? 'asc' : sortOrders['name'] < 0 ? 'des' : 'none'"></span></th>
                            <th @click="getSortedUserMovies('length')"      :class="{ active: sortKey == 'length' }"        class="toggle-header">  Length <span class="arrow"  :class="sortOrders['length'] > 0 ? 'asc' : sortOrders['length'] < 0 ? 'des' : 'none'"></span></th>
                            <th @click="getSortedUserMovies('releaseYear')" :class="{ active: sortKey == 'releaseYear' }"   class="toggle-header2"> Year <span class="arrow"    :class="sortOrders['releaseYear'] > 0 ? 'asc' : sortOrders['releaseYear'] < 0 ? 'des' : 'none'"></span></th>
                            <th @click="getSortedUserMovies('rating')"      :class="{ active: sortKey == 'rating' }"        class="toggle-header">  Rating <span class="arrow"  :class="sortOrders['rating'] > 0 ? 'asc' : sortOrders['rating'] < 0 ? 'des' : 'none'"></span></th>
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
                                <code>{{ userMovie.length }}</code>
                            </td>
                            <!-- Year -->
                            <td style="vertical-align: middle;" class="toggle-column2">
                                <code>{{ userMovie.releaseYear }}</code>
                            </td>
                            <!-- Rating -->
                            <td style="vertical-align: middle;" class="toggle-column">
                                <code>{{ userMovie.rating }}</code>
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
                            <div class="form-group">
                                <label class="col-md-3 control-label">Title</label>

                                <div class="col-md-7">
                                    <input id="create-movie-title" type="text" class="form-control"
                                                                @keyup.enter="store" v-model="createForm.title">

                                    <span class="help-block">
                                        Full title of movie.
                                    </span>
                                </div>
                            </div>
                            
                            <!-- Format -->
                            <div class="form-group">
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
                                </div>
                            </div>
                            
                            <!-- Length -->
                            <div class="form-group">
                                <label class="col-md-3 control-label">Length</label>

                                <div class="col-md-7">
                                    <input type="text" class="form-control" name="length"
                                                    @keyup.enter="store" v-model="createForm.length">

                                    <span class="help-block">
                                        The length of the movie in minutes.
                                    </span>
                                </div>
                            </div>

                            <!-- Year -->
                            <div class="form-group">
                                <label class="col-md-3 control-label">Year</label>

                                <div class="col-md-7">
                                    <input type="text" class="form-control" name="year"
                                                    @keyup.enter="store" v-model="createForm.year">

                                    <span class="help-block">
                                        Year movie was released.
                                    </span>
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
                            <div class="form-group">
                                <label class="col-md-3 control-label">Name</label>

                                <div class="col-md-7">
                                    <input id="edit-movie-title" type="text" class="form-control"
                                                                @keyup.enter="update" v-model="editForm.title">

                                    <span class="help-block">
                                        Full title of movie.
                                    </span>
                                </div>
                            </div>

                            <!-- Format -->
                            <div class="form-group">
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
                                </div>
                            </div>

                            <!-- Length -->
                            <div class="form-group">
                                <label class="col-md-3 control-label">Length</label>

                                <div class="col-md-7">
                                    <input type="text" class="form-control" name="length"
                                                    @keyup.enter="store" v-model="editForm.length">

                                    <span class="help-block">
                                        The length of the movie in minutes.
                                    </span>
                                </div>
                            </div>

                            <!-- Year -->
                            <div class="form-group">
                                <label class="col-md-3 control-label">Year</label>

                                <div class="col-md-7">
                                    <input type="text" class="form-control" name="year"
                                                    @keyup.enter="store" v-model="editForm.year">

                                    <span class="help-block">
                                        Year movie was released.
                                    </span>
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
                    length: '',
                    year: '',
                    rating: ''
                },

                editForm: {
                    errors: [],
                    title: '',
                    formatId: '', 
                    length: '',
                    year: '',
                    rating: ''
                }, 

                sortOrders : {},

                sortKey : '',
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

        methods: {
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
            getUserMovies(orderBy) 
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
                                            var temp = Object.keys(this.userMovies[0]);
                                            for (var i=0; i<temp.length; i++) 
                                            {
                                                this.sortOrders[temp[i]] = 0;
                                            }   
                                        }                                          
                                    });
                        });
            },

            getSortedUserMovies(orderBy) 
            {
                //TODO: fix direction...it is only going asc and none
                this.sortOrders[orderBy] == 0 ? this.sortOrders[orderBy] = -1 : this.sortOrders[orderBy] < 0 ? this.sortOrders[orderBy] = 1 : this.sortOrders[orderBy] = 0;
                var direction = this.sortOrders[orderBy] > 0 ? 'asc' : this.sortOrders[orderBy] < 0 ? 'dsc' : 'none';
                axios.get('/api/get-user-movies?orderBy='+orderBy+"&direction="+direction)
                        .then(response => 
                        {
                            this.userMovies = response.data.success.userMovies;
                            
                            if(this.userMovies[0] !== null)
                            {
                                
                                //reset sortOrders array
                                var temp = Object.keys(this.sortOrders);
                                for (var i=0; i<temp.length; i++)                                
                                {
                                    this.sortOrders[temp[i]] = 0        
                                } 
                                
                                this.sortOrders[response.data.success.sorting['orderBy']] = response.data.success.sorting['direction'] == 'asc' ? 1 : -1; //TODO put in none

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
            store() {
                this.persistMovie(
                    'post', 'api/create-movie',
                    this.createForm, '#modal-create-movie'
                );
            },




            /**
             * Edit the given movie.
             */
            edit(userMovie) {
                this.editForm.movieId = userMovie.id;
                this.editForm.title = userMovie.title;
                this.editForm.formatId = userMovie.formatId;
                this.editForm.length = userMovie.length;
                this.editForm.year = userMovie.releaseYear;
                this.editForm.rating = userMovie.rating;

                $('#modal-edit-movie').modal('show');
            },

            /**
             * Update the movie being edited.
             */
            update() {
                this.persistMovie(
                    'put', '/api/update-movie/' + this.editForm.movieId,
                    this.editForm, '#modal-edit-movie'
                );
            },

            /**
             * Persist the movie to storage using the given form.
             */
            persistMovie(method, uri, form, modal) {
                form.errors = [];

                axios[method](uri, form)
                    .then(response => {
                        this.getUserMovies();

                        form.title = '';
                        form.formatId = '';
                        form.Length = '';
                        form.year = '';
                        form.rating = '';
                        form.errors = [];

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
            destroy(userMovie) {
                axios.delete('/api/delete-movie/' + userMovie.id) //userMovie.id is movieId
                        .then(response => {
                            this.getUserMovies();
                        });
            }
        }
    }
</script>
