"use strict";var languageOptionsWrapper=$(".language-options"),languageOptions=".i18n-lang-option",changeText=$(".card-localization p");i18next.addResourceBundle("en_p","translation",{key:"Cake sesame snaps cupcake gingerbread danish I love gingerbread. Apple pie pie jujubes chupa chups muffin halvah lollipop. Chocolate cake oat cake tiramisu marzipan sugar plum. Donut sweet pie oat cake dragée fruitcake cotton candy lemon drops."}),i18next.addResourceBundle("pt_p","translation",{key:"O sésamo do bolo agarra dinamarquês do pão-de-espécie do queque eu amo o pão-de-espécie. Torta de torta de maçã jujubes chupa chups  pirulito halvah muffin. Ameixa do açúcar do maçapão do tiramisu do bolo da aveia do bolo de chocolate. Donut doce aveia torta  dragée fruitcake algodão doce gotas de limão."}),i18next.addResourceBundle("fr_p","translation",{key:"Gâteau au sésame s'enclenche petit pain au pain d'épices danois J'adore le pain d'épices. Tarte aux pommes jujubes chupa chups  muffin halva sucette. Gateau au chocolat gateau d  'avoine tiramisu prune sucre. Donut tourte sucrée gateau dragée fruit gateau barbe a papa citron gouttes.."}),i18next.addResourceBundle("de_p","translation",{key:"Kuchen Sesam Snaps Cupcake Lebkuchen dänisch Ich liebe Lebkuchen. Apfelkuchen Jujubes Chupa Chups Muffin Halwa Lutscher. Schokoladenkuchen-Haferkuchen-Tiramisumarzipanzuckerpflaume. Donut süße Torte Hafer Kuchen Dragée Obstkuchen Zuckerwatte Zitronentropfen."}),languageOptionsWrapper.length&&languageOptionsWrapper.on("click",languageOptions,(function(){var e=$(this).data("lng");i18next.changeLanguage(e,(function(e,a){changeText.localize()}))}));
