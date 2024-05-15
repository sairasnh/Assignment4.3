(function(api) {

  api.sectionConstructor['digitiva-upsell'] = api.Section.extend({
      attachEvents: function() {},
      isContextuallyActive: function() {
          return true;
      }
  });

  const digitiva_section_lists = ['banner', 'service'];
  digitiva_section_lists.forEach(digitiva_homepage_scroll);

  function digitiva_homepage_scroll(item) {
      item = item.replace(/-/g, '_');
      wp.customize.section('digitiva_' + item + '_section', function(section) {
          section.expanded.bind(function(isExpanding) {
              wp.customize.previewer.send(item, { expanded: isExpanding });
          });
      });
  }
})(wp.customize);