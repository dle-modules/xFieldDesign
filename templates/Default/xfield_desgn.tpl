[text] 
	{title} : <input {name} rows="25" {required} value="{val_input}"> <br>
[/text] 

[textarea]  
	[editor]<textarea {name} {params} {required} rows="15">{val_input}</textarea>[/editor] <br>
[/textarea] 

[select] 
	<select {name}>{val_input}</select> <br>
[/select]

[xf:test2]
	<textarea {name} {params} {required} rows="15">{val_input}</textarea> <br>
[/xf]

[xf:test3]
	{title} : <textarea {name} {params} {required} rows="25">{val_input}</textarea> <br>
[/xf]

[xf:test]
	{title} : <input {name} rows="25" {required} value="{val_input}"> <br>
[/xf]

[xf:test4]
	{title} : <select {name}>{val_input}</select>
[/xf]