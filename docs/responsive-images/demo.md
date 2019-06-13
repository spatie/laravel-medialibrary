---
title: Responsive images demo
weight: 5
---

{{< rawhtml >}}
  <img style="margin-bottom: 2rem" 
      srcset="../../images/demo/responsive-images/2400.jpg 2400w,
          ../../images/demo/responsive-images/1200.jpg 1200w,
          ../../images/demo/responsive-images/600.jpg 600w,
          ../../images/demo/responsive-images/300.jpg 300w,
          ../../images/demo/responsive-images/150.jpg 150w,
          data:image/svg+xml;base64,PCFET0NUWVBFIHN2ZyBQVUJMSUMgIi0vL1czQy8vRFREIFNWRyAxLjEvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkIj48c3ZnIHZlcnNpb249IjEuMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgeG1sOnNwYWNlPSJwcmVzZXJ2ZSIgeD0iMCIgeT0iMCIgdmlld0JveD0iMCAwIDI0MDAgMTU4OSI+ICAgIDxpbWFnZSB3aWR0aD0iMjQwMCIgaGVpZ2h0PSIxNTg5IiB4bGluazpocmVmPSJkYXRhOmltYWdlL2pwZWc7YmFzZTY0LC85ai80UUFZUlhocFpnQUFTVWtxQUFnQUFBQUFBQUFBQUFBQUFQL3NBQkZFZFdOcmVRQUJBQVFBQUFBeUFBRC80UU9CYUhSMGNEb3ZMMjV6TG1Ga2IySmxMbU52YlM5NFlYQXZNUzR3THdBOFAzaHdZV05yWlhRZ1ltVm5hVzQ5SXUrN3Z5SWdhV1E5SWxjMVRUQk5jRU5sYUdsSWVuSmxVM3BPVkdONmEyTTVaQ0kvUGlBOGVEcDRiWEJ0WlhSaElIaHRiRzV6T25nOUltRmtiMkpsT201ek9tMWxkR0V2SWlCNE9uaHRjSFJyUFNKQlpHOWlaU0JZVFZBZ1EyOXlaU0ExTGpZdFl6QTJOeUEzT1M0eE5UYzNORGNzSURJd01UVXZNRE12TXpBdE1qTTZOREE2TkRJZ0lDQWdJQ0FnSUNJK0lEeHlaR1k2VWtSR0lIaHRiRzV6T25Ka1pqMGlhSFIwY0RvdkwzZDNkeTUzTXk1dmNtY3ZNVGs1T1M4d01pOHlNaTF5WkdZdGMzbHVkR0Y0TFc1ekl5SStJRHh5WkdZNlJHVnpZM0pwY0hScGIyNGdjbVJtT21GaWIzVjBQU0lpSUhodGJHNXpPbmh0Y0UxTlBTSm9kSFJ3T2k4dmJuTXVZV1J2WW1VdVkyOXRMM2hoY0M4eExqQXZiVzB2SWlCNGJXeHVjenB6ZEZKbFpqMGlhSFIwY0RvdkwyNXpMbUZrYjJKbExtTnZiUzk0WVhBdk1TNHdMM05VZVhCbEwxSmxjMjkxY21ObFVtVm1JeUlnZUcxc2JuTTZlRzF3UFNKb2RIUndPaTh2Ym5NdVlXUnZZbVV1WTI5dEwzaGhjQzh4TGpBdklpQjRiWEJOVFRwUGNtbG5hVzVoYkVSdlkzVnRaVzUwU1VROUlqTkdNekJCT1RNeU9VSXhRekJHUlVNMk1EaEdOek5FTURVeU5ERTFOVEF4SWlCNGJYQk5UVHBFYjJOMWJXVnVkRWxFUFNKNGJYQXVaR2xrT2pZMVFqSTFORVUwUkRKRVJURXhSVGM1TVRrM1JURXpPVEkyUlVGR09EaENJaUI0YlhCTlRUcEpibk4wWVc1alpVbEVQU0o0YlhBdWFXbGtPalkxUWpJMU5FVXpSREpFUlRFeFJUYzVNVGszUlRFek9USTJSVUZHT0RoQ0lpQjRiWEE2UTNKbFlYUnZjbFJ2YjJ3OUlrRmtiMkpsSUZCb2IzUnZjMmh2Y0NCRFF5QXlNREUxSUUxaFkybHVkRzl6YUNJK0lEeDRiWEJOVFRwRVpYSnBkbVZrUm5KdmJTQnpkRkpsWmpwcGJuTjBZVzVqWlVsRVBTSjRiWEF1YVdsa09qQmhNR1E0WVRCakxXWmlPVFF0TkdVd1ppMWlOMk01TFRjNVpqSmxaRFJoT1RFeE55SWdjM1JTWldZNlpHOWpkVzFsYm5SSlJEMGlZV1J2WW1VNlpHOWphV1E2Y0dodmRHOXphRzl3T21ZNVltSmtNVGRtTFRGaU5EY3RNVEUzWWkxaE5EVTVMVGc1Wm1NME9EZ3hORFEyWkNJdlBpQThMM0prWmpwRVpYTmpjbWx3ZEdsdmJqNGdQQzl5WkdZNlVrUkdQaUE4TDNnNmVHMXdiV1YwWVQ0Z1BEOTRjR0ZqYTJWMElHVnVaRDBpY2lJL1B2L3VBQTVCWkc5aVpRQmt3QUFBQUFILzJ3Q0VBQWdHQmdZR0JnZ0dCZ2dNQ0FjSURBNEtDQWdLRGhBTkRRNE5EUkFSREE0TkRRNE1FUThTRXhRVEVnOFlHQm9hR0JnaklpSWlJeWNuSnljbkp5Y25KeWNCQ1FnSUNRb0pDd2tKQ3c0TERRc09FUTRPRGc0UkV3ME5EZzBORXhnUkR3OFBEeEVZRmhjVUZCUVhGaG9hR0JnYUdpRWhJQ0VoSnljbkp5Y25KeWNuSi8vQUFCRUlBQlVBSUFNQklnQUNFUUVERVFIL3hBQm5BQUFCQkFNQUFBQUFBQUFBQUFBQUFBQUFBUVFHQndJREJRRUFBd0VBQUFBQUFBQUFBQUFBQUFBQUFBRUNBeEFBQVFNQ0J3RUFBQUFBQUFBQUFBQUFBQUVDQXdRRkVTRXhRUkl5QmhNUkFRRUFBZ01BQUFBQUFBQUFBQUFBQUFBQlFRSlJnWkgvMmdBTUF3RUFBaEVERVFBL0FJalEzVGc1TXlYV3owRFdZWXVLd2plNU5COURWeU0wVXcyMG1FeGROUDZlTkdkaHJXZW9aczRxMWx5bVJPeW1FbHdsZHE1U1pyZVRybFIvTTNweDJBQXZhdkM1aVpnQTRILy8yUT09Ij4gICAgPC9pbWFnZT48L3N2Zz4= 32w"
      sizes="1px" 
      width="2400"        
      src="../../images/demo/responsive-images/2400.jpg"
      oonload="this.onload=null;this.sizes='(min-width: '+window.innerWidth+'px) '+Math.ceil(this.getBoundingClientRect().width/window.innerWidth*100)+'vw, 100vw';"
  
      onload="this.onload=null;this.sizes=Math.ceil(this.getBoundingClientRect().width/window.innerWidth*100)+'vw';"
  >
  <article class="article">
      <p>
          The example above demonstrates the <a href="https://docs.spatie.be/laravel-medialibrary/v7/responsive-images/getting-started-with-responsive-images">responsive images technique</a> used in the Laravel Medialibrary.
      </p>
      <h3>Try this:</h3>
      <ul>
          <li>Throttle your network in Chrome and disable the cache to see this in full effect</li>
          <li>Start with a small browser and reload this page</li>
          <li>Make your browser larger to start loading bigger versions</li>
      </ul> 
      <h3>What happens?</h3>
      <ul>
          <li>
              We start with an image that has <code>srcset</code> values, rendered by the <a href="https://docs.spatie.be/laravel-medialibrary/v7">Laravel Medialibrary</a>
          </li>
          <li>
              An initial <code>sizes="1px"</code> atribute is used to render an inline base64-encoded SVG placeholder first
          </li>
          <li>
              Once the page is loaded, JavaScript sets the <code>sizes</code> to the actual width of the image in the layout
          </li>
          <li>
              Since we use a <code>vw</code> value for this width, a bigger image will be loaded when you upscale the browser
          </li>
      </ul>
  </article>
{{< /rawhtml >}}