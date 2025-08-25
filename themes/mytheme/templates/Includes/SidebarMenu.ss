<div>
<ul class="list-unstyled">
	<% loop KategoriProduk %>		
	<li class="mb-3 d-flex align-items-center">
		<img src="$Image.URL" alt="$Nama" class="me-2" style="width: 50px; height: 50px;">
		<a href="$BaseHref?Kategori=$ID" class="text-decoration-none text-black">$Nama</a>
	</li>
	<% end_loop %>
</ul>
</div>