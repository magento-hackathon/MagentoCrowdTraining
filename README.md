# MagentoCrowdTraining

##Tutorial Module Overview

The tutorial module layout file

( app/design/adminhtml/base/default/layout/mediotype/tutorials/layout.xml )

updates the <default> handle for the admin layout to use the foundation_base handle from the Mediotype core adding angular and foundation to all admin pages.

It also adds to blocks to the right off canvas menu for the tutorial system and registers the javascript needed for the tutorial system with this call:

<reference name="angular.scripts">
    <block type="adminhtml/template" template="mediotype/tutorials/tutorials.phtml"></block>
</reference>
The tutorials.phtml file actually loads the contents of
app/design/frontend/base/default/template/mediotype/tutorials/tutorials.js
I’m not sure what series of events lead to the javascript being loaded this way, but this needs to be fixed.
How the Javascript works

The tutorial system builds on top of Foundation 5’s Joyride Component. Each step in the tutorial is associated with an element using css selectors. Unfortunately the Joyride component requires element Id’s so before it starts playing each element for each step is checked for an id, if it does not have one, one is generated and assigned to it then  the Joyride component plays as normal.

There are javascript callbacks that can be associated with steps and pages

beforePage, afterPage, beforeStep and afterStep

these can be used when scripting a tutorial to trigger css and events.

##Mediotype Core Overview as it relates to Foundation 5 and Magento:

The Mediotype_Core module layout file

( app/design/adminhtml/base/default/layout/mediotype/core/core.xml )

defines a handle called foundation_base.

foundation_base adds the following items to the head block
mediotype/core/foundation/modernizr.js
mediotype/core/jquery/jquery-2.1.1.min.js
mediotype/core/foundation/fastclick.js
mediotype/core/foundation/foundation.min.js
mediotype/core/angular/angular.min.js
mediotype/core/foundation/foundation.css
mediotype/core/foundation/foundation-icons.css
it also sets the template for the root block to mediotype/core/foundation/page.phtml

The foundation/page.phtml template is a copy of the stock magento adminhtml page.phtml file but it adds an extra block named “angular.scripts”  to the head that aggregates angular directives.

it also wraps the page in the Foundation 5 off canvas menu component and adds “offcanvas.right” and “offcanvas.left” blocks for inserting off canvas content.

More information about Fondation 5 Off Canvas here: http://foundation.zurb.com/docs/components/offcanvas.html