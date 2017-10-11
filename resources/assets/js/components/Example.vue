<template>
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading">Example Component</div>

                    <div class="panel-body">
                        I'm an example component!
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default 
    {
        mounted() 
        {
            console.log('Component mounted.')

            $.ajaxSetup({
            headers:
            { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
        });

            $.ajax(
            {
            
                url: '/api/user',
                type: 'get',                
                success: function(response)
                {
                    console.log(response.data);
                }
            });
           
 
        }
    }
</script>
