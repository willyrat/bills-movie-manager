<style scoped>
    .action-link {
        cursor: pointer;
    }

    .m-b-none {
        margin-bottom: 0;
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
                            <th>Id</th>
                            <th>Title</th>
                            <th>Length</th>
                            <th>Year Released</th>
                            <th>Rating</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="userMovie in userMovies">
                            <!-- ID -->
                            
                            <td style="vertical-align: middle;" >
                                {{ userMovie.id }}                                
                            </td>

                            <!-- title -->
                            <td style="vertical-align: middle;">
                                {{ userMovie.title }}
                            </td>

                            <!-- length -->
                            <td style="vertical-align: middle;">
                                <code>{{ userMovie.length }}</code>
                            </td>
                            <!-- Year -->
                            <td style="vertical-align: middle;">
                                <code>{{ userMovie.releaseYear }}</code>
                            </td>
                            <!-- Rating -->
                            <td style="vertical-align: middle;">
                                <code>{{ userMovie.rating }}</code>
                            </td>

                            <!-- Edit Button -->
                            <td style="vertical-align: middle;">
                                <a class="action-link" @click="edit(userMovie)">
                                    Edit
                                </a>
                            </td>

                            <!-- Delete Button -->
                            <td style="vertical-align: middle;">
                                <a class="action-link text-danger" @click="destroy(userMovie)">
                                    Delete
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

                createForm: {
                    errors: [],
                    title: '',
                    length: '',
                    year: '',
                    rating: ''
                },

                editForm: {
                    errors: [],
                    title: '',
                    length: '',
                    year: '',
                    rating: ''
                }
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
            getUserMovies() {
                axios.get('/api/get-user-movies')
                        .then(response => {
                            this.userMovies = response.data.success.userMovies;
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
                axios.delete('/api/delete-movie/' + userMovie.id)
                        .then(response => {
                            this.getUserMovies();
                        });
            }
        }
    }
</script>
