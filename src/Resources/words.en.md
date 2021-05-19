

Collected at first from wikipedia pages with

    var s=""; $("table.wikitable tr a").each(function(t, e){s+="- "+$(e).text()+"\n"}); s;

    var s = '';
    var lis = document.querySelectorAll("ol > li");
    for (li of lis) { s += "- " + li.innerText + "\n"; }
    s;
    
This is a good place to start contributing if you do not code.
- curate
- add words
- move adjectives to their proper category
- no duplicates
- no `-`, `,`, or `'`.
- basically just [a-z0-9] but don't end _beings_ with numbers.


Default usernames will be generated as follows :
<adjective1> <adjective2> <being> <random_number>


Order of adjectives in english :
- Quantity or number
- Quality or opinion
- Size
- Age
- Shape
- Color
- Proper adjective (often nationality, other place of origin, or material)
- Purpose or qualifier

When we make a full username, we randomly pick two adjectives and a being,
and we make sure the adjectives are in the right order, as follows :
quantity quality size age shape color origin material qualifier
