const blog_name = () => {
  window.chorusAnalytics_blogName = ajax_object.blog_name
  document.write(
    window.chorusAnalytics_blogName
  )
}

const data_single = () => {
  if(Array.isArray(ajax_object.data) && ajax_object.data.length) {
    window.chorusAnalytics_postType              =  ajax_object.data.post_type
    window.chorusAnalytics_postDate              =  ajax_object.data.post_date
    window.chorusAnalytics_postAuthor            =  ajax_object.data.post_author
    window.chorusAnalytics_postCategories        =  ajax_object.data.categories_json
    window.chorusAnalytics_postTags              =  ajax_object.data.tags_json
    window.chorusAnalytics_postWordCount         =  ajax_object.data.word_count
    window.chorusAnalytics_postPublishedByStudio =  ajax_object.data.published_by_studio
    document.write(
      window.chorusAnalytics_postType,             
      window.chorusAnalytics_postDate,             
      window.chorusAnalytics_postAuthor,           
      window.chorusAnalytics_postCategories,       
      window.chorusAnalytics_postTags,             
      window.chorusAnalytics_postWordCount,        
      window.chorusAnalytics_postPublishedByStudio
    )
  }
}

const rock_analytics_script = () => {
  window.onload = function () {
    if (!window.chorusAnalytics_isLoaded) {
      var rockAnalyticsScript = document.createElement('script')
      rockAnalyticsScript.type = 'text/javascript'
      rockAnalyticsScript.src = ajax_object.data.rock_analytics_script + ''
      rockAnalyticsScript.async = true
      document.body.appendChild(rockAnalyticsScript)
    }
  }
}

blog_name()
data_single()
rock_analytics_script()