<?php

namespace App\View\Components;

use Illuminate\View\Component;

class SvgIcons extends Component
{
    public $name;       // 圖示名稱
    public $classes;    // 自定義樣式類別
    public $extraData;  // 額外參數

    /**
     * Create a new component instance.
     *
     * @param string $name
     * @param string $classes
     * @param array $extraData
     */
    public function __construct($name, $classes = '', $extraData = [])
    {
        $this->name = $name;
        $this->classes = $classes;
        $this->extraData = $extraData ?: []; // 若為 null 則設為空陣列
    }

    /**
     * Render the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('components.svg-icons');
    }

    /**
     * Map of all available SVG icons.
     *
     * @return array
     */
    public function svgMap($classes) // 將 $classes 作為參數傳入
    {
        return [
            'edit' =>       '<svg xmlns="http://www.w3.org/2000/svg" class="' . $classes . '" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>',
            'delete' =>     '<svg xmlns="http://www.w3.org/2000/svg" class="' . $classes . '" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>',
            'dot' =>        '<svg width="24px" height="24px" viewBox="0 0 12.00 12.00" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="#ff0000" stroke-width="0.00012000000000000002"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"><path fill-rule="evenodd" clip-rule="evenodd" d="M12 4.5C7.30558 4.5 3.5 8.30558 3.5 13S7.30558 21.5 12 21.5 20.5 17.6944 20.5 13 16.6944 4.5 12 4.5zM12 6.042A6.968 6.968 0 0118 13a6.968 6.968 0 01-6 6.958V6.042z" fill="#ff0000"></path></g></svg>',
            'statusT' =>     '<svg width="24px" height="24px" viewBox="0 0 12.00 12.00" xmlns="http://www.w3.org/2000/svg" fill="#00ff00" stroke="#00ff00" stroke-width="0.00012000000000000002"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round" stroke="#CCCCCC" stroke-width="0.9359999999999999"></g><g id="SVGRepo_iconCarrier"> <path fill-rule="evenodd" clip-rule="evenodd" d="M6 12A6 6 0 106 0a6 6 0 000 12zm2.576-7.02a.75.75 0 00-1.152-.96L5.45 6.389l-.92-.92A.75.75 0 003.47 6.53l1.5 1.5a.75.75 0 001.106-.05l2.5-3z" fill="#00ff00"></path> </g></svg>',
            'statusF' =>     '<svg width="24px" height="24px" viewBox="0 0 12.00 12.00" xmlns="http://www.w3.org/2000/svg" fill="#ff0000" stroke="#ff0000" stroke-width="0.00012000000000000002"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path fill-rule="evenodd" clip-rule="evenodd" d="M6 12A6 6 0 106 0a6 6 0 000 12zM3 5a1 1 0 000 2h6a1 1 0 100-2H3z" fill="#ff0000"></path> </g></svg>',
            'key'   =>      '<svg width="24px" height="24px" viewBox="-4.86 -4.86 58.31 58.31" xmlns="http://www.w3.org/2000/svg" fill="#000000" version="1.1" id="Capa_1" xmlns:xlink="http://www.w3.org/1999/xlink" xml:space="preserve"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round" stroke="#CCCCCC" stroke-width="1.5548800000000003"></g><g id="SVGRepo_iconCarrier"> <g> <g> <path d="M29.462,11.82c0-2.85-2.318-5.168-5.168-5.168c-2.849,0-5.167,2.318-5.167,5.168s2.318,5.168,5.167,5.168 C27.144,16.988,29.462,14.67,29.462,11.82z M24.293,15.088c-1.801,0-3.267-1.466-3.267-3.268c0-1.801,1.466-3.267,3.267-3.267 c1.802,0,3.268,1.466,3.268,3.267C27.562,13.622,26.096,15.088,24.293,15.088z"></path> <path d="M27.681,23.139c5.003-1.488,8.436-6.039,8.436-11.318C36.116,5.303,30.812,0,24.293,0c-6.517,0-11.82,5.303-11.82,11.82 c0,5.009,3.216,9.491,7.905,11.144V48.59h7.302V45.9h8.078v-6.857h-8.078v-1.104h8.078v-6.856h-8.078V23.139z M33.858,32.984 v3.055H25.78v4.904h8.078v3.056H25.78v2.69h-3.5V21.552l-0.69-0.196c-4.249-1.21-7.216-5.129-7.216-9.536 c0-5.469,4.451-9.92,9.92-9.92c5.47,0,9.921,4.451,9.921,9.92c0,4.57-3.237,8.633-7.698,9.659l-0.736,0.17v11.335H33.858z"></path> </g> </g> </g></svg>',
            'list'  =>      '<svg width="24px" height="24px" xmlns="http://www.w3.org/2000/svg" class="$classes text-gray-800 dark:text-gray-100 " fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16m-7 6h7" /></svg> ',
            'enable' =>     '<svg width="24px" height="24px" viewBox="0 0 12.00 12.00" xmlns="http://www.w3.org/2000/svg" fill="#00ff00" stroke="#00ff00" stroke-width="0.00012000000000000002"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round" stroke="#CCCCCC" stroke-width="0.9359999999999999"></g><g id="SVGRepo_iconCarrier"> <path fill-rule="evenodd" clip-rule="evenodd" d="M6 12A6 6 0 106 0a6 6 0 000 12zm2.576-7.02a.75.75 0 00-1.152-.96L5.45 6.389l-.92-.92A.75.75 0 003.47 6.53l1.5 1.5a.75.75 0 001.106-.05l2.5-3z" fill="#00ff00"></path> </g></svg>',
            'disenable' =>  '<svg width="24px" height="24px" viewBox="0 0 12.00 12.00" xmlns="http://www.w3.org/2000/svg" fill="#ff0000" stroke="#ff0000" stroke-width="0.00012000000000000002"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path fill-rule="evenodd" clip-rule="evenodd" d="M6 12A6 6 0 106 0a6 6 0 000 12zM3 5a1 1 0 000 2h6a1 1 0 100-2H3z" fill="#ff0000"></path> </g></svg>',
            'logo'      =>  '<svg xmlns="http://www.w3.org/2000/svg" class="' . $classes . '" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <circle cx="100" cy="100" r="90" stroke="gray" stroke-width="15" fill="none" />
                                <polygon points="100,20 170,140 30,140" stroke="gray" stroke-width="15" fill="none" />
                                <polygon points="100,180 170,60 30,60" stroke="gray" stroke-width="15" fill="none" />
                            </svg>',
            'Floppy'    =>  '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-floppy" viewBox="0 0 16 16">
                            <path d="M11 2H9v3h2z"/>
                            <path d="M1.5 0h11.586a1.5 1.5 0 0 1 1.06.44l1.415 1.414A1.5 1.5 0 0 1 16 2.914V14.5a1.5 1.5 0 0 1-1.5 1.5h-13A1.5 1.5 0 0 1 0 14.5v-13A1.5 1.5 0 0 1 1.5 0M1 1.5v13a.5.5 0 0 0 .5.5H2v-4.5A1.5 1.5 0 0 1 3.5 9h9a1.5 1.5 0 0 1 1.5 1.5V15h.5a.5.5 0 0 0 .5-.5V2.914a.5.5 0 0 0-.146-.353l-1.415-1.415A.5.5 0 0 0 13.086 1H13v4.5A1.5 1.5 0 0 1 11.5 7h-7A1.5 1.5 0 0 1 3 5.5V1H1.5a.5.5 0 0 0-.5.5m3 4a.5.5 0 0 0 .5.5h7a.5.5 0 0 0 .5-.5V1H4zM3 15h10v-4.5a.5.5 0 0 0-.5-.5h-9a.5.5 0 0 0-.5.5z"/>
                            </svg>',
            'config'    =>  '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-gear-wide-connected" viewBox="0 0 16 16"><path d="M7.068.727c.243-.97 1.62-.97 1.864 0l.071.286a.96.96 0 0 0 1.622.434l.205-.211c.695-.719 1.888-.03 1.613.931l-.08.284a.96.96 0 0 0 1.187 1.187l.283-.081c.96-.275 1.65.918.931 1.613l-.211.205a.96.96 0 0 0 .434 1.622l.286.071c.97.243.97 1.62 0 1.864l-.286.071a.96.96 0 0 0-.434 1.622l.211.205c.719.695.03 1.888-.931 1.613l-.284-.08a.96.96 0 0 0-1.187 1.187l.081.283c.275.96-.918 1.65-1.613.931l-.205-.211a.96.96 0 0 0-1.622.434l-.071.286c-.243.97-1.62.97-1.864 0l-.071-.286a.96.96 0 0 0-1.622-.434l-.205.211c-.695.719-1.888.03-1.613-.931l.08-.284a.96.96 0 0 0-1.186-1.187l-.284.081c-.96.275-1.65-.918-.931-1.613l.211-.205a.96.96 0 0 0-.434-1.622l-.286-.071c-.97-.243-.97-1.62 0-1.864l.286-.071a.96.96 0 0 0 .434-1.622l-.211-.205c-.719-.695-.03-1.888.931-1.613l.284.08a.96.96 0 0 0 1.187-1.186l-.081-.284c-.275-.96.918-1.65 1.613-.931l.205.211a.96.96 0 0 0 1.622-.434zM12.973 8.5H8.25l-2.834 3.779A4.998 4.998 0 0 0 12.973 8.5m0-1a4.998 4.998 0 0 0-7.557-3.779l2.834 3.78zM5.048 3.967l-.087.065zm-.431.355A4.98 4.98 0 0 0 3.002 8c0 1.455.622 2.765 1.615 3.678L7.375 8zm.344 7.646.087.065z"/></svg>'
        ];
    }

    /**
     * Retrieve the SVG content for the given name.
     *
     * @return string
     */
    public function getSvg($classes = ' h-6 w-6 ')
    {
        $svgs = $this->svgMap($classes);
        return $svgs[$this->name] ?? '';
    }
}
