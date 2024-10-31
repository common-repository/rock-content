jQuery(document).ready( function($) {
  let check = false;
  let closed_ids = [];
  let count_load = 0;
  let notifications_data = [];
  let timeout = null;
  
  const start_screen = () => {
    show_loading("flex");
    load_posts('new_and_old');
  }

  $.fn.isInViewport = function() {
    const elementTop = $(this).offset().top;
    const elementBottom = elementTop + $(this).outerHeight();

    const viewportTop = $(window).scrollTop();
    const viewportBottom = viewportTop + $(window).height();

    return elementBottom > viewportTop && (elementTop + 200) < (viewportBottom);
  }

  const state_check = (state) => {
    check = state;
    return check;
  }

  const verify_count = () => {
    const num_notification = count_notification();
    const num_notification_db = count_notification_db();
    const display_notification = num_notification > 0 ? num_notification : num_notification_db;
    if(num_notification < 1 && num_notification_db < 1) {
      hide_notification_count();
    }else{
      $('.rcp_count_internal').html(display_notification);
      $('.rcp-notification-count').html(display_notification < 100 ? display_notification : '+99');
    }
  }

  const transfer_children_for_olds = () => {
    hide_notification_count();
    const previous_notifications = $('.rcp_previous_box_notifications');
    const new_notifications = $('.rcp_new_notifications');
    hide_rcp_checks();
    new_notifications.fadeOut(500);
    $('.rcp_previous_notifications').fadeIn();
    $('.rcp_previous_box_notifications').fadeIn();
    previous_notifications.delay(400).queue(() => { 
      change_state_whitout_notification("flex");

      previous_notifications.append(...new_notifications.children());
    });
  }

  const count_notification_db = () => {
    return $('.rcp-notification-count').html();
  }

  const show_new_header = () => {
    $('.news_checkall').show();
    $('.rcp_title_new').show();
  }

  const verify_notifications_size = () => {
    const box_notification_children = $('.rcp_previous_box_notifications').children().length > 0;
    if($('.rcp_new_notifications').children().length < 1) {
      change_state_whitout_notification("flex");
      if(box_notification_children) {
        show_new_header();
        hide_rcp_checks();
      }else {
        $('.news_checkall').hide();
      }
    }else {
      change_state_whitout_notification("none");
      $(".rcp_new_notifications").show();
      show_new_header();
    }
    if(box_notification_children) {
      $('.rcp_previous_notifications').show();
      $('.rcp_previous_box_notifications ').show();
    }
    verify_count();
  }

  const add_notifications  = (data, type_notification) => {
    if(type_notification == 'old'){
      change_count_load(5, '+');
      $('.rcp_previous_box_notifications').append(data);
    }else if(type_notification == 'new') {
      $('.rcp_new_notifications').append(data);
    }else{
      change_count_load(5, '+');
      show_loading("none");
      $('.rcp_new_notifications').append(data[0]);
      $('.rcp_previous_box_notifications').append(data[1]);
      notifications_data = data[2];
      verify_notifications_size();
    }
  }

  const load_posts = (type_notification) => {
    $.ajax({
      url: ajax_object.ajax_url,
      type: 'post',
      data: {
        action: 'rcpLoadNotifications',
        rock_content_notification_nonce: ajax_object.nonce,
        closed_ids: closed_ids,
        number_notifications: count_load,
        type_notification: type_notification
      },
      success : ( data ) => {
        add_notifications(data, type_notification);
        state_check(false);
      },
      dataType:"json"
    })
  }

  const search_mode = () => {
    $('.news_checkall').hide();
    $('.rcp_previous_notifications').hide();
    $('.rcp_previous_box_notifications ').hide();
  }

  const hide_rcp_checks = () => {
    $('.rcp_check').hide();
    $('.rcp_checkall_mob').hide();
  }

  const clear_box_notifications = () => {
    $(".rcp_new_notifications").html("");
    $(".rcp_previous_box_notifications").html("");
  }

  const count_notification = () => {
    return $('.rcp_new_notifications').children().length;
  }

  const show_loading = (mode) => {
    clear_box_notifications();
    change_state_whitout_notification("none");
    search_mode();
    $(".rcp_loader_wrapper").css("display", mode);
  }

  const change_count_load = (val, operator) => {
    if(operator == '+') {
      count_load += val;
    }else {
      count_load -= val;
    }
  }

  const normal_mode = () => {
    change_count_load(count_load, '-');
    closed_ids = [];
    show_loading("none");
    load_posts('new_and_old');

    const num_notification = count_notification();
    if(num_notification > 0) {
      $('.news_checkall').show();
    }
    $('.rcp_without_notification').show();
    num_notification < 1 ? change_state_whitout_notification("flex") : change_state_whitout_notification("none");
  }

  const show_notification = (searched_notifications) => {
    show_loading("none");
    if(searched_notifications.length === 0) {
      change_state_whitout_notification("flex");
      $(".rcp_new_notifications").hide();
    }else{
      $(".rcp_new_notifications").show();
      change_state_whitout_notification("none");
      $.ajax({
        url: ajax_object.ajax_url,
        type: 'post',
        data: {
          action: 'rcpShowSearchedNotifications',
          rock_content_notification_nonce: ajax_object.nonce,
          searched_notifications: searched_notifications
        },
        success : ( data ) => {
          add_notifications(data, 'new');
        }
      })
    }
  }

  const normalize_string = (text) => {
    return text.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, "");
  }

  const filter_notifications = (search) => {
    const filtered_notifications = notifications_data.filter((o) => {
      return normalize_string(o.title).includes(search)
      || normalize_string(o.message).includes(search);
    });

    return filtered_notifications;
  }

  const search_notification = (search) => {
    $(".rcp_new_notifications").html("");
    search = normalize_string(search);
    const filtered_notifications = filter_notifications(search);
    show_notification(filtered_notifications);
  }

  const change_state_whitout_notification = (state) => {
    $(".rcp_box_whitout_notification").css("display", state);
  }

  $('.rcp-box-notification').on('scroll', function() {
    const scrollY = $(this)[0].scrollHeight - $(this).scrollTop();
    const height = $(this).outerHeight();
    const offset = height - scrollY;
    const search_val = search_is_empty();
    const isInViewport = $('.rcp_previous_box_notifications').isInViewport();
    if (offset == 0 && !check && search_val) {
      state_check(true);
      load_posts('old');
    }
    $('.rcp_previous_box_notifications').css("opacity",  isInViewport ? "1" : "0.7");
  });

  $('.rcp_search').keyup(function(){
    const search = this.value;
    show_loading("flex");
    const search_val = search_is_empty();
    clearTimeout(timeout)
    timeout = setTimeout(() => {
      if(search_val) {
        normal_mode();
      }else {
        search_notification(search);
      }
    }, 1000);
  });

  $('#rcp_checkall').click(function(){
    $.ajax({
      url: ajax_object.ajax_url,
      type: 'post',
      data: {
        action: 'hide_all_notification',
        rock_content_notification_nonce: ajax_object.nonce,
      },
      success : ( data ) => {
        notifications_data = data[0];
        closed_ids.push(...data[1]);
      },
      dataType:"json"
    });
    transfer_children_for_olds();
  });

  const change_count_after_close = () => {
    const count = count_notification() - 1;
    if(count > 0){
      $('.rcp_count_internal').html(count);
      if(count < 100){
        $('.rcp-notification-count').html(count);
      }
    }else{
      hide_notification_count();
    }
  }

  const hide_notification_count = () => {
    $(".rcp-notification-count").css("height", "18px");
    $(".rcp-notification-count").css("background-color", "transparent");
    $(".rcp-notification-count").css("border", "none");
    $('.rcp-notification-count').html('');
    $(".rcp_count_internal").css("background-color", "transparent");
    $(".rcp_count_internal").css("border", "none");
    $('.rcp_count_internal').html('');
  }

  const read_notification = (box_close) => {
    const search_val = search_is_empty();
    if(!($('#rcp_checkall').is(":checked"))){
      let box_notification = box_close.parent().parent().parent();
      const class_check = box_notification.attr('class');
      if(class_check != 'rcp_notification_box') {
        box_notification = box_notification.parent();
      }
      if(search_val) {
        box_notification.fadeOut(500);
        box_notification.delay(400).queue(() => { 
          box_notification.appendTo('.rcp_previous_box_notifications');
          box_notification.css("display", "flex");
        });
      }

    }
    box_close.parent().hide();
    if(search_val) {
      $('.rcp_previous_notifications').fadeIn();
      $('.rcp_previous_box_notifications').fadeIn();
    }
  }

  const search_is_empty = () => {
    return $('.rcp_search').val().length === 0;
  }

  const new_without_childs = () => {
    if ( $('.rcp_new_notifications').children().length - 1 < 1 ) {
      hide_rcp_checks();
      $(".rcp_box_whitout_notification").delay(800).queue(() => { 
        change_state_whitout_notification("flex");
      });
    }
  }

  $(".rcp_new_notifications").on("click", ".rcp_notification_close", function() {
    const clicked = $(this).attr("data-id");
    const box_close = $(this);
    const search_val = search_is_empty();
    if(search_val) {
        closed_ids.push(clicked);
    }
    change_count_after_close();
    read_notification(box_close);
    new_without_childs();

    $.ajax({
      url: ajax_object.ajax_url,
      type: 'post',
      data: {
        action: 'hide_notification',
        rock_content_notification_nonce: ajax_object.nonce,
        already_read: 1,
        id_notification: clicked
      },
      success : ( data ) => {
        notifications_data = data;
      },
      dataType:"json"
    });

  });

  start_screen();
});