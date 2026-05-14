<!DOCTYPE html>
<html>
<head>
    <title>Laravel 12 TinyMCE AJAX CRUD</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables CSS (Optional) -->
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
    <h2 class="mb-4">Laravel 12 CRUD with TinyMCE & Bootstrap 5</h2>
    <a class="btn btn-success mb-3" href="javascript:void(0)" id="createNewPost"> Create New Post</a>

    <!-- Place the first <script> tag in your HTML's <head> -->
<script src="https://cdn.tiny.cloud/1/c1awec2ci3eqv25l3zt4gkqyn3z0l5qog0rjadqtpygf9ahg/tinymce/8/tinymce.min.js" referrerpolicy="origin" crossorigin="anonymous"></script>

<!-- Place the following <script> and <textarea> tags your HTML's <body> -->
<script>
  tinymce.init({
    selector: 'textarea',
    plugins: [
      // Core editing features
      'anchor', 'autolink', 'charmap', 'codesample', 'emoticons', 'link', 'lists', 'media', 'searchreplace', 'table', 'visualblocks', 'wordcount',
      // Your account includes a free trial of TinyMCE premium features
      // Try the most popular premium features until Dec 14, 2025:
      'checklist', 'mediaembed', 'casechange', 'formatpainter', 'pageembed', 'a11ychecker', 'tinymcespellchecker', 'permanentpen', 'powerpaste', 'advtable', 'advcode', 'advtemplate', 'ai', 'uploadcare', 'mentions', 'tinycomments', 'tableofcontents', 'footnotes', 'mergetags', 'autocorrect', 'typography', 'inlinecss', 'markdown','importword', 'exportword', 'exportpdf'
    ],
    toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link media table mergetags | addcomment showcomments | spellcheckdialog a11ycheck typography uploadcare | align lineheight | checklist numlist bullist indent outdent | emoticons charmap | removeformat',
    tinycomments_mode: 'embedded',
    tinycomments_author: 'Author name',
    mergetags_list: [
      { value: 'First.Name', title: 'First Name' },
      { value: 'Email', title: 'Email' },
    ],
    ai_request: (request, respondWith) => respondWith.string(() => Promise.reject('See docs to implement AI Assistant')),
    uploadcare_public_key: 'cb4fe3eca4140256f94d',
  });
</script>
<textarea>
  Welcome to TinyMCE!
</textarea>

    <table class="table table-bordered data-table">
        <thead>
            <tr>
                <th>No</th>
                <th>Title</th>
                <th width="280px">Action</th>
            </tr>
        </thead>
        <tbody>
            <!-- Data loaded via AJAX/DataTables -->
        </tbody>
    </table>
</div>

<!-- Bootstrap 5 Modal -->
<div class="modal fade" id="ajaxModel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="modelHeading"></h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="postForm" name="postForm" class="form-horizontal">
                   <input type="hidden" name="post_id" id="post_id">

                    <div class="mb-3">
                        <label for="title" class="form-label">Title</label>
                        <input type="text" class="form-control" id="title" name="title" placeholder="Enter Title" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Content</label>
                        <!-- TinyMCE Target -->
                        <textarea id="tinyEditor" name="body" class="form-control"></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary" id="saveBtn" value="create">Save changes</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<!-- TinyMCE CDN -->
{{-- <script src="https://cdn.tiny.cloud/1/c1awec2ci3eqv25l3zt4gkqyn3z0l5qog0rjadqtpygf9ahg/tinymce/8/tinymce.min.js" referrerpolicy="origin" crossorigin="anonymous"></script> --}}
{{-- <script src="https://cdn.tiny.cloud/1/c1awec2ci3eqv25l3zt4gkqyn3z0l5qog0rjadqtpygf9ahg/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script> --}}

<script type="text/javascript">
  $(function () {

    // 1. Initialize TinyMCE
    tinymce.init({
        selector: '#tinyEditor',
        height: 300,
        plugins: 'code table lists',
        toolbar: 'undo redo | blocks | bold italic | alignleft aligncenter alignright | indent outdent | bullist numlist | code | table'
    });

    // 2. Setup CSRF Token for AJAX
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // 3. Initialize DataTable
    var table = $('.data-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('posts.index') }}",
        columns: [
            {data: 'id', name: 'id'},
            {data: 'title', name: 'title'},
            {data: 'action', name: 'action', orderable: false, searchable: false},
        ]
    });

    // 4. Open Modal for Create
    $('#createNewPost').click(function () {
        $('#saveBtn').val("create-post");
        $('#post_id').val('');
        $('#postForm').trigger("reset");
        $('#modelHeading').html("Create New Post");

        // Reset TinyMCE content
        if (tinymce.get('tinyEditor')) {
            tinymce.get('tinyEditor').setContent('');
        }

        $('#ajaxModel').modal('show');
    });

    // 5. Open Modal for Edit
    $('body').on('click', '.edit', function () {
        var post_id = $(this).data('id');
        $.get("{{ route('posts.index') }}" +'/' + post_id +'/edit', function (data) {
            $('#modelHeading').html("Edit Post");
            $('#saveBtn').val("edit-user");
            $('#ajaxModel').modal('show');
            $('#post_id').val(data.id);
            $('#title').val(data.title);

            // Set TinyMCE content from database
            if (tinymce.get('tinyEditor')) {
                tinymce.get('tinyEditor').setContent(data.body);
            }
        });
    });

    // 6. Handle Save (Create/Update)
    $('#saveBtn').click(function (e) {
        e.preventDefault();
        $(this).html('Sending..');

        // CRITICAL: Sync TinyMCE content to the textarea before serializing
        tinymce.triggerSave();

        $.ajax({
            data: $('#postForm').serialize(),
            url: "{{ route('posts.store') }}",
            type: "POST",
            dataType: 'json',
            success: function (data) {
                $('#postForm').trigger("reset");
                $('#ajaxModel').modal('hide');
                table.draw();
                $('#saveBtn').html('Save changes');
            },
            error: function (data) {
                console.log('Error:', data);
                $('#saveBtn').html('Save changes');
            }
        });
    });

    // 7. Handle Delete
    $('body').on('click', '.delete', function () {
        var post_id = $(this).data("id");
        if(confirm("Are You sure want to delete !")){
            $.ajax({
                type: "DELETE",
                url: "{{ route('posts.store') }}"+'/'+post_id,
                success: function (data) {
                    table.draw();
                },
                error: function (data) {
                    console.log('Error:', data);
                }
            });
        }
    });
  });
</script>
</body>
</html>
