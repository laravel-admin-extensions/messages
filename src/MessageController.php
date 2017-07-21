<?php

namespace Encore\Admin\Message;

use Carbon\Carbon;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Controllers\ModelForm;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Message\Widgets\MarkAsRead;
use Encore\Admin\Message\Widgets\MessageType;

class MessageController
{
    use ModelForm;

    /**
     * Index interface.
     *
     * @return Content
     */
    public function index()
    {
        return Admin::content(function (Content $content) {
            $content->header('Messages');
            $content->description('Message list..');

            $content->body($this->grid());
        });
    }

    /**
     * Create interface.
     *
     * @return Content
     */
    public function create()
    {
        return Admin::content(function (Content $content) {
            $content->header('header');
            $content->description('description');

            $content->body($this->form());
        });
    }

    public function grid()
    {
        return Admin::grid(MessageModel::class, function (Grid $grid) {

            $type = request()->get('type', 'inbox');

            $grid->model()->with('sender')->orderBy('id', 'desc')->{$type}();

            $grid->sender()->name('From');
            $grid->title()->display(function ($title) {
                return "<a href='#' data-toggle=\"modal\" data-target=\"#messageModal\" data-id='{$this->id}' data-from='{$this->sender['name']}' data-title='{$this->title}' data-message='{$this->message}' data-time='{$this->created_at}'>$title</a>";
            });
            $grid->message()->limit(40);

            $grid->created_at()->display(function ($time) {
                return Carbon::parse($time)->diffForHumans();
            });

            $grid->filter(function ($filter) {
                $filter->disableIdFilter();
                $filter->like('title');
                $filter->like('message');

                $filter->between('created_at')->datetime();
            });

            $grid->tools(function (Grid\Tools $tools) {
                $tools->append(new MessageType());
                $tools->append($this->messageModal());
            });

            $grid->actions(function (Grid\Displayers\Actions $actions) {

                $actions->disableEdit();

                $url = $actions->getResource() . '/create?';

                $url .= http_build_query([
                    'title' => "Re:" . $actions->row->title,
                    'to'    => $actions->row->from
                ]);

                $actions->prepend("<a class=\"btn btn-xs\" href=\"$url\"><i class=\"fa fa-reply\"></i></a>");
            });

            if ($type == 'inbox') {
                $grid->rows(function (Grid\Row $row) {
                    if (is_null($row->read_at)) {
                        $row->setAttributes(['style' => 'font-weight: 700;']);
                    }
                });

                $grid->tools(function ($tools) {
                    $tools->batch(function (Grid\Tools\BatchActions $batch) {
                        $batch->add('Mark as read', new MarkAsRead());
                    });

                });
            }
        });
    }

    public function form()
    {
        return Admin::form(MessageModel::class, function (Form $form) {

            $options = Administrator::where('id', '!=', Admin::user()->id)->get()->pluck('name', 'id');
            $defaults = [request('to')];

            $form->multipleSelect('to')->options($options)->default($defaults);
            $form->text('title')->rules('required')->default(request('title'));
            $form->textarea('message')->rules('required');

            $form->display('created_at');
        });
    }

    public function update($id)
    {
        $ids = explode(',', $id);

        MessageModel::inbox()->whereIn('id', $ids)->update(['read_at' => Carbon::now()]);

        return [
            'status'  => true,
            'message' => '更新成功',
        ];
    }

    protected function messageModal()
    {
        $path = trim(request()->path(), '/');

        $script = <<<SCRIPT

$('#messageModal').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget);
    var from = button.data('from');
    var title = button.data('title');
    var message = button.data('message');
    var time = button.data('time');

    var modal = $(this);
    modal.find('.modal-title').text(title);
    modal.find('.modal-body #message-from').val(from);
    modal.find('.modal-body #message-title').val(title);
    modal.find('.modal-body #message-text').val(message);
    modal.find('.modal-body #message-time').val(time);

    $.ajax({
        method: 'put',
        url: '/{$path}/' + button.data('id'),
        data: {
            _token:LA.token,
        }
    });

}).on('hide.bs.modal', function (event) {
    $.pjax.reload('#pjax-container');
});

SCRIPT;

        Admin::script($script);

        return <<<MODAL
<div class="modal fade" id="messageModal" tabindex="-1" role="dialog" aria-labelledby="messageModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="messageModalLabel">New message</h4>
      </div>
      <div class="modal-body">
        <form>
          <div class="form-group">
            <label for="message-from" class="control-label">From:</label>
            <input type="text" class="form-control" id="message-from">
          </div>
          <div class="form-group">
            <label for="message-title" class="control-label">Title:</label>
            <input type="text" class="form-control" id="message-title">
          </div>
          <div class="form-group">
            <label for="message-text" class="control-label">Message:</label>
            <textarea class="form-control" id="message-text" rows=8></textarea>
          </div>
          <div class="form-group">
            <label for="message-time" class="control-label">Time:</label>
            <input type="text" class="form-control" id="message-time">
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
MODAL;

    }
}
