module ExtJS4
  module SassExtensions
    module Functions
      module Utils
        def parsebox(list, n)
          assert_type n, :Number
          if !n.int?
            raise ArgumentError.new("List index #{n} must be an integer")
          elsif n.to_i < 1
            raise ArgumentError.new("List index #{n} must be greater than or equal to 1")
          elsif n.to_i > 4
            raise ArgumentError.new("A box string can't contain more then 4")
          end

          new_list = list.clone.to_a
          size = new_list.size
                      
          if n.to_i >= size
            if size == 1
              new_list[1] = new_list[0]
              new_list[2] = new_list[0]
              new_list[3] = new_list[0]
            elsif size == 2
              new_list[2] = new_list[0]
              new_list[3] = new_list[1]
            elsif size == 3
              new_list[3] = new_list[1]
            end
          end
          
          new_list.to_a[n.to_i - 1]
        end
        
        def parseint(value)
          Sass::Script::Number.new(value.to_i)
        end
        
        def theme_image(theme, path, without_url = false)
          path = path.value
          
          images_path = File.join($ext_path, 'resources', 'themes', 'images', theme.value)
          image_path = File.join(images_path, path)
          
          if !without_url
            url = "url('#{image_path}')"
          else
            url = "#{image_path}"
          end
          
          Sass::Script::String.new(url)
        end
      end
    end
  end
end

module Sass::Script::Functions
  include ExtJS4::SassExtensions::Functions::Utils
end