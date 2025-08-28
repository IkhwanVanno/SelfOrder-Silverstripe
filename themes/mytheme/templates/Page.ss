<!DOCTYPE html>
<html lang="$ContentLocale">
<head>
	<% base_tag %>
	<title><% if $MetaTitle %>$MetaTitle<% else %>$Title<% end_if %> &raquo; $SiteConfig.Title</title>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	$MetaTags(false)
	<link rel="shortcut icon" href="$SiteConfig.Logo.URL" />
	<script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#8AA624',
                        secondary: '#FEA405',
                        accent: '#DBE4C9',
                        light: '#FFFFF0'
                    }
                }
            }
        }
    </script>
    <script>
      setTimeout(() => {
        const flash = document.querySelector('.fixed.top-4.right-4');
        if (flash) flash.remove();
      }, 5000);
    </script>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
</head>
<body class="$ClassName.ShortName<% if not $Menu(2) %> no-sidebar<% end_if %>" <% if $i18nScriptDirection %>dir="$i18nScriptDirection"<% end_if %>%>
  <% include Header %>

  <!-- Wrapper Layout -->
  <div class="flex flex-col md:flex-row min-h-screen" role="main">
    
    <!-- Sidebar -->
    <div class="w-full md:w-1/4 lg:w-1/5 bg-accent text-black p-4">
      <% include SideBar %>
    </div>

    <!-- Konten -->
    <div class="flex-1 bg-light p-6">
      $Layout
    </div>

    <!-- Flash Messages -->
    <% if $FlashMessages %>
      <div class="fixed top-4 right-4 z-50">
        <div class="flex items-start gap-4 p-4 rounded-lg shadow-lg text-white
          <% if $FlashMessages.Type == 'success' %> bg-green-600 
          <% else_if $FlashMessages.Type == 'error' %> bg-red-600 
          <% else_if $FlashMessages.Type == 'warning' %> bg-yellow-500 
          <% else %> bg-gray-800 
          <% end_if %>">
          
          <div class="flex-1">
            $FlashMessages.Message
          </div>

          <button onclick="this.parentElement.parentElement.remove()" class="text-white hover:text-gray-300 text-xl leading-none font-bold">
            &times;
          </button>
        </div>
      </div>
    <% end_if %>
  </div>

  <% include Footer %>
</body>
</html>
